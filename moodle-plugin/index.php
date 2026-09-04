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
 * Interactive dashboard for modular synchronization and teacher mapping.
 *
 * @package    local_dapodik
 * @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
admin_externalpage_setup('localdapodiksync');
require_capability('local/dapodik:sync', context_system::instance());

$PAGE->set_url(new moodle_url('/local/dapodik/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_dapodik'));
$PAGE->set_heading(get_string('control_center_title', 'local_dapodik'));

$tab = optional_param('tab', 'sync', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('control_center_title', 'local_dapodik'));

// Navigation tabs.
echo '<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link ' . ($tab === 'sync' ? 'active' : '') . '" href="index.php?tab=sync">⚡ ' . get_string('tab_sync', 'local_dapodik') . '</a>
  </li>
  <li class="nav-item">
    <a class="nav-link ' . ($tab === 'mapping' ? 'active' : '') . '" href="index.php?tab=mapping">👨‍🏫 ' . get_string('tab_mapping', 'local_dapodik') . '</a>
  </li>
</ul>';

$manager = new \local_dapodik\sync_manager();

// =============================================================================
// TAB 1: MODULAR SYNCHRONIZATION
// =============================================================================
if ($tab === 'sync') {
    if ($action === 'dosync' && confirm_sesskey()) {
        $options = [
            'students'     => (bool) optional_param('sync_students', 0, PARAM_INT),
            'teachers'     => (bool) optional_param('sync_teachers', 0, PARAM_INT),
            'cohorts'      => (bool) optional_param('sync_cohorts', 0, PARAM_INT),
            'courses'      => (bool) optional_param('sync_courses', 0, PARAM_INT),
            'auto_teacher' => (bool) optional_param('auto_teacher', 0, PARAM_INT),
        ];

        echo $OUTPUT->notification(get_string('sync_started', 'local_dapodik'), 'info');

        try {
            $stats = $manager->sync_modular($options, function($msg) {
                echo html_writer::div(s($msg), 'alert alert-secondary py-1 px-2 my-1');
                flush();
            });

            echo $OUTPUT->notification(get_string('sync_success', 'local_dapodik'), 'success');
            echo html_writer::tag('pre', json_encode($stats, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            echo $OUTPUT->notification(get_string('sync_failed', 'local_dapodik', $e->getMessage()), 'error');
        }

        echo html_writer::link(new moodle_url('/local/dapodik/index.php?tab=sync'), get_string('btn_back', 'local_dapodik'), ['class' => 'btn btn-secondary mt-3']);
    } else {
        echo '<div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title">' . get_string('modular_sync_heading', 'local_dapodik') . '</h5>
                <p class="text-muted">' . get_string('modular_sync_desc', 'local_dapodik') . '</p>
                <form method="post" action="index.php?tab=sync&action=dosync">
                    <input type="hidden" name="sesskey" value="' . sesskey() . '">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_students" value="1" id="chkStudents" checked>
                        <label class="form-check-label" for="chkStudents">
                            <strong>' . get_string('sync_students', 'local_dapodik') . '</strong> - ' . get_string('opt_students', 'local_dapodik') . '
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_teachers" value="1" id="chkTeachers" checked>
                        <label class="form-check-label" for="chkTeachers">
                            <strong>' . get_string('sync_teachers', 'local_dapodik') . '</strong> - ' . get_string('opt_teachers', 'local_dapodik') . '
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_cohorts" value="1" id="chkCohorts" checked>
                        <label class="form-check-label" for="chkCohorts">
                            <strong>' . get_string('sync_cohorts', 'local_dapodik') . '</strong> - ' . get_string('opt_cohorts', 'local_dapodik') . '
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_courses" value="1" id="chkCourses" checked>
                        <label class="form-check-label" for="chkCourses">
                            <strong>' . get_string('sync_courses', 'local_dapodik') . '</strong> - ' . get_string('opt_courses', 'local_dapodik') . '
                        </label>
                    </div>
                    <div class="form-check mb-4 ms-4 border-start ps-3 py-1 bg-light rounded">
                        <input class="form-check-input" type="checkbox" name="auto_teacher" value="1" id="chkAutoTeacher">
                        <label class="form-check-label" for="chkAutoTeacher">
                            <em>' . get_string('opt_auto_teacher', 'local_dapodik') . '</em><br />
                            <small class="text-muted">' . get_string('opt_auto_teacher_desc', 'local_dapodik') . '</small>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">🚀 ' . get_string('btn_run_sync', 'local_dapodik') . '</button>
                </form>
            </div>
        </div>';
    }
}

// =============================================================================
// TAB 2: TEACHER ASSIGNMENT DASHBOARD
// =============================================================================
if ($tab === 'mapping') {
    // Handle manual assignment action.
    if ($action === 'assign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->assign_teacher_to_course($courseid, $teacherid)) {
            echo $OUTPUT->notification(get_string('msg_assign_success', 'local_dapodik'), 'success');
        } else {
            echo $OUTPUT->notification(get_string('msg_assign_error', 'local_dapodik'), 'error');
        }
    }

    // Handle manual unassignment.
    if ($action === 'unassign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->unassign_teacher_from_course($courseid, $teacherid)) {
            echo $OUTPUT->notification(get_string('msg_unassign_success', 'local_dapodik'), 'info');
        }
    }

    $dapodikCourses = $manager->get_dapodik_courses_list();
    $availableTeachers = $manager->get_available_teachers();

    echo '<div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">' . get_string('mapping_heading', 'local_dapodik') . '</h5>
            <p class="text-muted mb-0">' . get_string('mapping_desc', 'local_dapodik') . '</p>
        </div>
        <span class="badge bg-primary fs-6">' . get_string('badge_courses_count', 'local_dapodik', count($dapodikCourses)) . '</span>
    </div>';

    if (empty($dapodikCourses)) {
        echo $OUTPUT->notification(get_string('no_courses_yet', 'local_dapodik'), 'warning');
    } else {
        echo '<div class="table-responsive">
            <table class="table table-hover table-bordered align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 30%;">' . get_string('th_course', 'local_dapodik') . '</th>
                        <th style="width: 20%;">' . get_string('th_guru_dapodik', 'local_dapodik') . '</th>
                        <th style="width: 25%;">' . get_string('th_guru_moodle', 'local_dapodik') . '</th>
                        <th style="width: 25%;">' . get_string('th_action', 'local_dapodik') . '</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($dapodikCourses as $item) {
            $course = $item['course'];
            $guruDapodik = $item['guru_dapodik'];
            $assignedTeachers = $item['assigned_teachers'];

            echo '<tr>';
            echo '<td><strong>' . s($course->fullname) . '</strong><br /><small class="text-muted">' . s($course->shortname) . '</small></td>';
            echo '<td><span class="badge bg-secondary">' . s($guruDapodik) . '</span></td>';

            // Current assigned teachers.
            echo '<td>';
            if (empty($assignedTeachers)) {
                echo '<span class="badge bg-warning text-dark">' . get_string('no_teacher_assigned', 'local_dapodik') . '</span>';
            } else {
                foreach ($assignedTeachers as $t) {
                    $unassignUrl = new moodle_url('/local/dapodik/index.php', [
                        'tab'       => 'mapping',
                        'action'    => 'unassign',
                        'courseid'  => $course->id,
                        'teacherid' => $t->id,
                        'sesskey'   => sesskey(),
                    ]);
                    echo '<div class="d-flex justify-content-between align-items-center mb-1 bg-light p-1 rounded">
                        <span>👤 ' . s($t->firstname . ' ' . $t->lastname) . ' (' . s($t->username) . ')</span>
                        <a href="' . $unassignUrl . '" class="btn btn-sm btn-outline-danger py-0 px-1" title="' . get_string('btn_unassign_title', 'local_dapodik') . '">&times;</a>
                    </div>';
                }
            }
            echo '</td>';

            // Assign new teacher dropdown form.
            echo '<td>
                <form method="post" action="index.php?tab=mapping&action=assign" class="d-flex gap-1">
                    <input type="hidden" name="sesskey" value="' . sesskey() . '">
                    <input type="hidden" name="courseid" value="' . $course->id . '">
                    <select name="teacherid" class="form-select form-select-sm" required>
                        <option value="">' . get_string('select_teacher_placeholder', 'local_dapodik') . '</option>';
            foreach ($availableTeachers as $at) {
                echo '<option value="' . $at->id . '">' . s($at->firstname . ' ' . $at->lastname) . ' (' . s($at->username) . ')</option>';
            }
            echo '    </select>
                    <button type="submit" class="btn btn-sm btn-success text-nowrap">➕ ' . get_string('btn_assign', 'local_dapodik') . '</button>
                </form>
            </td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}

echo $OUTPUT->footer();
