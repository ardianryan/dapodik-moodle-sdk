<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Core Synchronization Manager for local_dapodik.
 *
 * @package    local_dapodik
 * @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dapodik;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

/**
 * Sync Manager orchestrating data synchronization between Dapodik and Moodle.
 * Supports modular sync and manual teacher mapping.
 */
class sync_manager {
    protected dapodik_client $client;
    protected string $emaildomain;

    public function __construct(?dapodik_client $client = null) {
        $this->client = $client ?? new dapodik_client();
        $this->emaildomain = get_config('local_dapodik', 'email_domain') ?: 'sekolah.sch.id';
    }

    /**
     * Run modular synchronization based on options.
     *
     * @param array $options [
     *     'students'     => bool,
     *     'teachers'     => bool,
     *     'cohorts'      => bool,
     *     'courses'      => bool,
     *     'auto_teacher' => bool,
     * ]
     * @param callable|null $logger
     * @return array
     */
    public function sync_modular(array $options, ?callable $logger = null): array {
        $stats = [
            'students' => 0,
            'teachers' => 0,
            'cohorts'  => 0,
            'courses'  => 0,
        ];

        $log = $logger ?? function($msg) { mtrace($msg); };
        $log("Starting Dapodik Kemendikdasmen modular synchronization...");

        if (!empty($options['students'])) {
            $log("Syncing students (Peserta Didik)...");
            $stats['students'] = $this->sync_students($log);
        }

        if (!empty($options['teachers'])) {
            $log("Syncing teachers and staff (GTK)...");
            $stats['teachers'] = $this->sync_teachers($log);
        }

        if (!empty($options['cohorts'])) {
            $log("Syncing study groups (Cohorts)...");
            $stats['cohorts'] = $this->sync_cohorts($log);
        }

        if (!empty($options['courses'])) {
            $auto_teacher = !empty($options['auto_teacher']);
            $log("Syncing courses and subjects (" . ($auto_teacher ? "Auto-assigning teachers enabled" : "Manual teacher assignment mode") . ")...");
            $stats['courses'] = $this->sync_courses($auto_teacher, $log);
        }

        $log("Modular synchronization finished. Summary: " . json_encode($stats));
        return $stats;
    }

    /**
     * Run full synchronization based on plugin settings.
     */
    public function sync_all(?callable $logger = null): array {
        $options = [
            'students'     => (bool) get_config('local_dapodik', 'sync_students'),
            'teachers'     => (bool) get_config('local_dapodik', 'sync_teachers'),
            'cohorts'      => (bool) get_config('local_dapodik', 'sync_cohorts'),
            'courses'      => (bool) get_config('local_dapodik', 'sync_courses'),
            'auto_teacher' => false,
        ];
        return $this->sync_modular($options, $logger);
    }

    /**
     * Synchronize students into Moodle users (username = NISN).
     * Generates a unique secure initial password and forces password change on first login.
     */
    public function sync_students(?callable $log = null): int {
        global $DB, $CFG;
        $count = 0;
        $page = 1;
        $limit = 100;

        while (true) {
            $rows = $this->client->get_peserta_didik($page, $limit);
            if (empty($rows)) {
                break;
            }

            foreach ($rows as $pd) {
                $nisn = trim($pd['nisn'] ?? '');
                if (empty($nisn)) {
                    continue;
                }

                $username = strtolower($nisn);
                $fullname = trim($pd['nama'] ?? 'Siswa ' . $nisn);
                $names = explode(' ', $fullname, 2);
                $firstname = $names[0];
                $lastname = $names[1] ?? '.';

                $email = trim($pd['email'] ?? '');
                if (empty($email) || !validate_email($email)) {
                    $email = $username . '@' . $this->emaildomain;
                }

                $existing = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id]);

                if (!$existing) {
                    $random_password = bin2hex(random_bytes(10)) . '!Aa1';
                    $user = new \stdClass();
                    $user->auth = 'manual';
                    $user->confirmed = 1;
                    $user->mnethostid = $CFG->mnet_localhost_id;
                    $user->username = $username;
                    $user->password = hash_internal_user_password($random_password);
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->email = $email;
                    $user->idnumber = $nisn;
                    $user->institution = $pd['sekolah_id'] ?? '';
                    $user->department = 'Peserta Didik';
                    $user->lang = 'id';
                    $user->timecreated = time();
                    $user->timemodified = time();

                    $user_id = user_create_user($user, false, false);
                    set_user_preference('auth_forcepasswordchange', 1, $user_id);
                    $count++;
                } else {
                    $existing->firstname = $firstname;
                    $existing->lastname = $lastname;
                    $existing->idnumber = $nisn;
                    $existing->timemodified = time();
                    user_update_user($existing, false, false);
                }
            }

            if (count($rows) < $limit) {
                break;
            }
            $page++;
        }

        if ($log) $log("Total siswa diproses: $count");
        return $count;
    }

    /**
     * Synchronize teachers (GTK) into Moodle users.
     * Generates a unique secure initial password and forces password change on first login.
     */
    public function sync_teachers(?callable $log = null): int {
        global $DB, $CFG;
        $count = 0;
        $page = 1;
        $limit = 100;

        while (true) {
            $rows = $this->client->get_gtk($page, $limit);
            if (empty($rows)) {
                break;
            }

            foreach ($rows as $gtk) {
                $nip = trim($gtk['nip'] ?? '');
                $nik = trim($gtk['nik'] ?? '');
                $username = !empty($nip) ? $nip : (!empty($nik) ? $nik : 'gtk_' . ($gtk['ptk_id'] ?? ''));

                if (empty($username)) {
                    continue;
                }

                $username = strtolower($username);
                $fullname = trim($gtk['nama'] ?? 'Guru ' . $username);
                $names = explode(' ', $fullname, 2);
                $firstname = $names[0];
                $lastname = $names[1] ?? '.';

                $email = trim($gtk['email'] ?? '');
                if (empty($email) || !validate_email($email)) {
                    $email = $username . '@' . $this->emaildomain;
                }

                $existing = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id]);

                if (!$existing) {
                    $random_password = bin2hex(random_bytes(10)) . '!Aa1';
                    $user = new \stdClass();
                    $user->auth = 'manual';
                    $user->confirmed = 1;
                    $user->mnethostid = $CFG->mnet_localhost_id;
                    $user->username = $username;
                    $user->password = hash_internal_user_password($random_password);
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->email = $email;
                    $user->idnumber = $gtk['ptk_id'] ?? $username;
                    $user->department = 'Guru / GTK';
                    $user->lang = 'id';
                    $user->timecreated = time();
                    $user->timemodified = time();

                    $user_id = user_create_user($user, false, false);
                    set_user_preference('auth_forcepasswordchange', 1, $user_id);
                    $count++;
                }
            }

            if (count($rows) < $limit) {
                break;
            }
            $page++;
        }

        if ($log) $log("Total guru/tendik diproses: $count");
        return $count;
    }

    /**
     * Synchronize Rombel into Moodle Cohorts.
     */
    public function sync_cohorts(?callable $log = null): int {
        global $DB;
        $count = 0;
        $rombels = $this->client->get_rombongan_belajar();

        $syscontext = \context_system::instance();

        foreach ($rombels as $rombel) {
            $rombelid = $rombel['rombongan_belajar_id'] ?? '';
            $nama = trim($rombel['nama'] ?? '');
            if (empty($nama)) continue;

            $idnumber = 'ROMBEL_' . $rombelid;
            $cohort = $DB->get_record('cohort', ['idnumber' => $idnumber]);

            if (!$cohort) {
                $newcohort = new \stdClass();
                $newcohort->contextid = $syscontext->id;
                $newcohort->name = $nama;
                $newcohort->idnumber = $idnumber;
                $newcohort->description = 'Cohort Rombongan Belajar Dapodik: ' . $nama;
                $newcohort->component = 'local_dapodik';
                $cohortid = cohort_add_cohort($newcohort);
                $count++;
            } else {
                $cohortid = $cohort->id;
            }

            if (!empty($rombel['anggota_rombel']) && is_array($rombel['anggota_rombel'])) {
                foreach ($rombel['anggota_rombel'] as $anggota) {
                    $nisn = strtolower(trim($anggota['nisn'] ?? ''));
                    if (empty($nisn)) continue;

                    $user = $DB->get_record('user', ['username' => $nisn, 'deleted' => 0]);
                    if ($user && !cohort_is_member($cohortid, $user->id)) {
                        cohort_add_member($cohortid, $user->id);
                    }
                }
            }
        }

        if ($log) $log("Total cohort rombel disinkronkan: $count");
        return $count;
    }

    /**
     * Synchronize courses from Dapodik pembelajaran.
     *
     * @param bool $auto_assign_teacher Whether to automatically enrol the teacher defined in Dapodik.
     * @param callable|null $log
     * @return int
     */
    public function sync_courses(bool $auto_assign_teacher = false, ?callable $log = null): int {
        global $DB;
        $count = 0;
        $rombels = $this->client->get_rombongan_belajar();

        $category = $DB->get_record('course_categories', ['name' => 'Dapodik Courses']);
        if (!$category) {
            $cat = new \stdClass();
            $cat->name = 'Dapodik Courses';
            $cat->parent = 0;
            $catid = \core_course_category::create($cat)->id;
        } else {
            $catid = $category->id;
        }

        foreach ($rombels as $rombel) {
            $rombelname = trim($rombel['nama'] ?? '');
            $rombelid = $rombel['rombongan_belajar_id'] ?? '';
            if (empty($rombel['pembelajaran']) || !is_array($rombel['pembelajaran'])) {
                continue;
            }

            foreach ($rombel['pembelajaran'] as $pemb) {
                $mapel = trim($pemb['nama_mata_pelajaran'] ?? '');
                $pembid = $pemb['pembelajaran_id'] ?? '';
                $nama_guru = trim($pemb['nama_guru'] ?? $pemb['nama_ptk'] ?? '');
                $ptk_id = trim($pemb['ptk_id'] ?? '');

                if (empty($mapel) || empty($pembid)) continue;

                $shortname = substr($mapel . ' - ' . $rombelname, 0, 100);
                $idnumber = 'PEMB_' . $pembid;

                $existing = $DB->get_record('course', ['idnumber' => $idnumber]);
                if (!$existing) {
                    $course = new \stdClass();
                    $course->category = $catid;
                    $course->fullname = $mapel . ' (' . $rombelname . ')';
                    $course->shortname = $shortname;
                    $course->idnumber = $idnumber;
                    $course->summary = 'Mata pelajaran Dapodik: ' . $mapel . ' | Kelas: ' . $rombelname . ($nama_guru ? ' | Guru Dapodik: ' . $nama_guru : '');
                    $course->format = 'topics';
                    $course->numsections = 4;
                    $course->startdate = time();
                    $course->visible = 1;

                    $newcourse = create_course($course);
                    $courseid = $newcourse->id;
                    $count++;
                } else {
                    $courseid = $existing->id;
                }

                $cohort = $DB->get_record('cohort', ['idnumber' => 'ROMBEL_' . $rombelid]);
                if ($cohort) {
                    $this->enrol_cohort_to_course($courseid, $cohort->id);
                }

                if ($auto_assign_teacher && !empty($ptk_id)) {
                    $teacher_user = $DB->get_record('user', ['idnumber' => $ptk_id, 'deleted' => 0]);
                    if ($teacher_user) {
                        $this->assign_teacher_to_course($courseid, $teacher_user->id);
                    }
                }
            }
        }

        if ($log) $log("Total kursus disinkronkan: $count");
        return $count;
    }

    /**
     * Enrol a cohort to a course using enrol_cohort plugin.
     */
    protected function enrol_cohort_to_course(int $courseid, int $cohortid): void {
        global $DB;
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'cohort', 'customint1' => $cohortid]);
        if (!$instance) {
            $enrolplugin = enrol_get_plugin('cohort');
            if ($enrolplugin) {
                $course = $DB->get_record('course', ['id' => $courseid]);
                $studentrole = $DB->get_record('role', ['shortname' => 'student']);
                if ($studentrole && $course) {
                    $enrolplugin->add_instance($course, [
                        'customint1' => $cohortid,
                        'roleid'     => $studentrole->id,
                        'status'     => ENROL_INSTANCE_ENABLED,
                    ]);
                }
            }
        }
    }

    /**
     * Assign a user as teacher to a course.
     */
    public function assign_teacher_to_course(int $courseid, int $userid, string $roleshortname = 'editingteacher'): bool {
        global $DB;
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $role = $DB->get_record('role', ['shortname' => $roleshortname]);
        if (!$role) {
            $role = $DB->get_record('role', ['shortname' => 'teacher']);
        }
        if (!$role) return false;

        $enrolmanual = enrol_get_plugin('manual');
        if (!$enrolmanual) return false;

        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE);
        if (!$instance) {
            $instanceid = $enrolmanual->add_default_instance($course);
            $instance = $DB->get_record('enrol', ['id' => $instanceid]);
        }

        $enrolmanual->enrol_user($instance, $userid, $role->id, time());
        return true;
    }

    /**
     * Unassign a user from a course.
     */
    public function unassign_teacher_from_course(int $courseid, int $userid): bool {
        global $DB;
        $enrolmanual = enrol_get_plugin('manual');
        if (!$enrolmanual) return false;

        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE);
        if ($instance) {
            $enrolmanual->unenrol_user($instance, $userid);
            return true;
        }
        return false;
    }

    /**
     * Fetch list of all Dapodik courses with currently enrolled teachers.
     */
    public function get_dapodik_courses_list(): array {
        global $DB;
        $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber, c.summary
                  FROM {course} c
                 WHERE c.idnumber LIKE 'PEMB_%'
              ORDER BY c.fullname ASC";
        $courses = $DB->get_records_sql($sql);

        $result = [];
        foreach ($courses as $c) {
            $context = \context_course::instance($c->id);
            $teacherroles = $DB->get_records_list('role', 'shortname', ['editingteacher', 'teacher']);
            $roleids = array_keys($teacherroles);

            $assigned_teachers = [];
            if (!empty($roleids)) {
                list($insql, $inparams) = $DB->get_in_or_equal($roleids);
                $sql_teachers = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
                                   FROM {role_assignments} ra
                                   JOIN {user} u ON u.id = ra.userid
                                  WHERE ra.contextid = ? AND ra.roleid $insql AND u.deleted = 0";
                $assigned_teachers = $DB->get_records_sql($sql_teachers, array_merge([$context->id], $inparams));
            }

            $guru_dapodik = '-';
            if (preg_match('/Guru Dapodik:\s*(.+)$/i', $c->summary, $m)) {
                $guru_dapodik = trim($m[1]);
            }

            $result[] = [
                'course'            => $c,
                'guru_dapodik'      => $guru_dapodik,
                'assigned_teachers' => array_values($assigned_teachers),
            ];
        }

        return $result;
    }

    /**
     * Fetch list of all potential teachers available in Moodle.
     */
    public function get_available_teachers(): array {
        global $DB;
        $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.department
                  FROM {user} u
                 WHERE u.deleted = 0 AND u.suspended = 0 AND u.id > 2
              ORDER BY u.firstname ASC, u.lastname ASC";
        return array_values($DB->get_records_sql($sql));
    }
}
