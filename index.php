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

$manager = new \local_dapodik\sync_manager();

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
        $templatedata = [
            'is_sync'                  => true,
            'is_mapping'               => false,
            'sesskey'                  => sesskey(),
            'str_tab_sync'             => get_string('tab_sync', 'local_dapodik'),
            'str_tab_mapping'          => get_string('tab_mapping', 'local_dapodik'),
            'str_modular_sync_heading' => get_string('modular_sync_heading', 'local_dapodik'),
            'str_modular_sync_desc'    => get_string('modular_sync_desc', 'local_dapodik'),
            'str_sync_students'        => get_string('sync_students', 'local_dapodik'),
            'str_opt_students'         => get_string('opt_students', 'local_dapodik'),
            'str_sync_teachers'        => get_string('sync_teachers', 'local_dapodik'),
            'str_opt_teachers'         => get_string('opt_teachers', 'local_dapodik'),
            'str_sync_cohorts'         => get_string('sync_cohorts', 'local_dapodik'),
            'str_opt_cohorts'          => get_string('opt_cohorts', 'local_dapodik'),
            'str_sync_courses'         => get_string('sync_courses', 'local_dapodik'),
            'str_opt_courses'          => get_string('opt_courses', 'local_dapodik'),
            'str_opt_auto_teacher'     => get_string('opt_auto_teacher', 'local_dapodik'),
            'str_opt_auto_teacher_desc'=> get_string('opt_auto_teacher_desc', 'local_dapodik'),
            'str_btn_run_sync'         => get_string('btn_run_sync', 'local_dapodik'),
        ];
        echo $OUTPUT->render_from_template('local_dapodik/control_center', $templatedata);
    }
}

if ($tab === 'mapping') {
    if ($action === 'assign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->assign_teacher_to_course($courseid, $teacherid)) {
            echo $OUTPUT->notification(get_string('msg_assign_success', 'local_dapodik'), 'success');
        } else {
            echo $OUTPUT->notification(get_string('msg_assign_error', 'local_dapodik'), 'error');
        }
    }

    if ($action === 'unassign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->unassign_teacher_from_course($courseid, $teacherid)) {
            echo $OUTPUT->notification(get_string('msg_unassign_success', 'local_dapodik'), 'info');
        }
    }

    $dapodik_courses = $manager->get_dapodik_courses_list();
    $available_teachers_raw = $manager->get_available_teachers();

    $available_teachers = [];
    foreach ($available_teachers_raw as $at) {
        $available_teachers[] = [
            'id'       => $at->id,
            'fullname' => s($at->firstname . ' ' . $at->lastname),
            'username' => s($at->username),
        ];
    }

    $formatted_courses = [];
    foreach ($dapodik_courses as $item) {
        $course = $item['course'];
        $assigned_teachers_raw = $item['assigned_teachers'];

        $assigned_teachers = [];
        foreach ($assigned_teachers_raw as $t) {
            $unassign_url = new moodle_url('/local/dapodik/index.php', [
                'tab'       => 'mapping',
                'action'    => 'unassign',
                'courseid'  => $course->id,
                'teacherid' => $t->id,
                'sesskey'   => sesskey(),
            ]);
            $assigned_teachers[] = [
                'fullname'     => s($t->firstname . ' ' . $t->lastname),
                'username'     => s($t->username),
                'unassign_url' => $unassign_url->out(),
            ];
        }

        $formatted_courses[] = [
            'id'                => $course->id,
            'fullname'          => s($course->fullname),
            'shortname'         => s($course->shortname),
            'guru_dapodik'      => s($item['guru_dapodik']),
            'has_teachers'      => !empty($assigned_teachers),
            'assigned_teachers' => $assigned_teachers,
        ];
    }

    $templatedata = [
        'is_sync'                         => false,
        'is_mapping'                      => true,
        'sesskey'                         => sesskey(),
        'str_tab_sync'                    => get_string('tab_sync', 'local_dapodik'),
        'str_tab_mapping'                 => get_string('tab_mapping', 'local_dapodik'),
        'str_mapping_heading'             => get_string('mapping_heading', 'local_dapodik'),
        'str_mapping_desc'                => get_string('mapping_desc', 'local_dapodik'),
        'total_courses_label'             => get_string('badge_courses_count', 'local_dapodik', count($dapodik_courses)),
        'has_courses'                     => !empty($formatted_courses),
        'courses'                         => $formatted_courses,
        'available_teachers'              => $available_teachers,
        'str_th_course'                   => get_string('th_course', 'local_dapodik'),
        'str_th_guru_dapodik'             => get_string('th_guru_dapodik', 'local_dapodik'),
        'str_th_guru_moodle'              => get_string('th_guru_moodle', 'local_dapodik'),
        'str_th_action'                   => get_string('th_action', 'local_dapodik'),
        'str_no_teacher_assigned'         => get_string('no_teacher_assigned', 'local_dapodik'),
        'str_btn_unassign_title'          => get_string('btn_unassign_title', 'local_dapodik'),
        'str_select_teacher_placeholder'  => get_string('select_teacher_placeholder', 'local_dapodik'),
        'str_btn_assign'                  => get_string('btn_assign', 'local_dapodik'),
        'str_no_courses_yet'              => get_string('no_courses_yet', 'local_dapodik'),
    ];

    echo $OUTPUT->render_from_template('local_dapodik/control_center', $templatedata);
}

echo $OUTPUT->footer();
