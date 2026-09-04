<?php
// Core Synchronization Manager for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

namespace local_dapodik;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Sync Manager orchestrating data synchronization between Dapodik and Moodle.
 */
class sync_manager {
    protected dapodik_client $client;
    protected string $defaultpassword;
    protected string $emaildomain;

    public function __construct(?dapodik_client $client = null) {
        $this->client = $client ?? new dapodik_client();
        $this->defaultpassword = get_config('local_dapodik', 'default_password') ?: 'Dapodik@2026!';
        $this->emaildomain = get_config('local_dapodik', 'email_domain') ?: 'sekolah.sch.id';
    }

    /**
     * Run full synchronization based on plugin settings.
     */
    public function sync_all(?callable $logger = null): array {
        $stats = [
            'students' => 0,
            'teachers' => 0,
            'cohorts'  => 0,
            'courses'  => 0,
        ];

        $log = $logger ?? function($msg) { mtrace($msg); };

        $log("Starting Dapodik Kemendikdasmen Synchronization...");

        // 1. Sync Students.
        if (get_config('local_dapodik', 'sync_students')) {
            $log("Syncing Students (Peserta Didik)...");
            $stats['students'] = $this->sync_students($log);
        }

        // 2. Sync Teachers / GTK.
        if (get_config('local_dapodik', 'sync_teachers')) {
            $log("Syncing Teachers (GTK)...");
            $stats['teachers'] = $this->sync_teachers($log);
        }

        // 3. Sync Cohorts (Rombel).
        if (get_config('local_dapodik', 'sync_cohorts')) {
            $log("Syncing Cohorts (Rombongan Belajar)...");
            $stats['cohorts'] = $this->sync_cohorts($log);
        }

        // 4. Sync Courses & Enrolments.
        if (get_config('local_dapodik', 'sync_courses')) {
            $log("Syncing Courses & Enrolments from Pembelajaran...");
            $stats['courses'] = $this->sync_courses($log);
        }

        $log("Synchronization finished successfully! Summary: " . json_encode($stats));
        return $stats;
    }

    /**
     * Synchronize students into Moodle users (username = NISN).
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
                    $user = new \stdClass();
                    $user->auth = 'manual';
                    $user->confirmed = 1;
                    $user->mnethostid = $CFG->mnet_localhost_id;
                    $user->username = $username;
                    $user->password = hash_internal_user_password($this->defaultpassword);
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->email = $email;
                    $user->idnumber = $nisn;
                    $user->institution = $pd['sekolah_id'] ?? '';
                    $user->department = 'Peserta Didik';
                    $user->lang = 'id';
                    $user->timecreated = time();
                    $user->timemodified = time();

                    user_create_user($user, false, false);
                    $count++;
                } else {
                    // Update existing record if needed.
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

        if ($log) $log("Total students processed: $count");
        return $count;
    }

    /**
     * Synchronize teachers (GTK) into Moodle users.
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
                    $user = new \stdClass();
                    $user->auth = 'manual';
                    $user->confirmed = 1;
                    $user->mnethostid = $CFG->mnet_localhost_id;
                    $user->username = $username;
                    $user->password = hash_internal_user_password($this->defaultpassword);
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->email = $email;
                    $user->idnumber = $gtk['ptk_id'] ?? $username;
                    $user->department = 'Guru / GTK';
                    $user->lang = 'id';
                    $user->timecreated = time();
                    $user->timemodified = time();

                    user_create_user($user, false, false);
                    $count++;
                }
            }

            if (count($rows) < $limit) {
                break;
            }
            $page++;
        }

        if ($log) $log("Total teachers processed: $count");
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

            // Sync anggota rombel to cohort.
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

        if ($log) $log("Total cohorts synchronized: $count");
        return $count;
    }

    /**
     * Synchronize courses from Dapodik pembelajaran and enrol students & teachers.
     */
    public function sync_courses(?callable $log = null): int {
        global $DB;
        $count = 0;
        $rombels = $this->client->get_rombongan_belajar();

        // Ensure category exists.
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
            if (empty($rombel['pembelajaran']) || !is_array($rombel['pembelajaran'])) {
                continue;
            }

            foreach ($rombel['pembelajaran'] as $pemb) {
                $mapel = trim($pemb['nama_mata_pelajaran'] ?? '');
                $pembid = $pemb['pembelajaran_id'] ?? '';
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
                    $course->summary = 'Mata pelajaran Dapodik: ' . $mapel . ' untuk kelas ' . $rombelname;
                    $course->format = 'topics';
                    $course->numsections = 4;
                    $course->startdate = time();
                    $course->visible = 1;

                    create_course($course);
                    $count++;
                }
            }
        }

        if ($log) $log("Total courses synchronized: $count");
        return $count;
    }
}
