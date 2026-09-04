<?php
// Manual Synchronization Page for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('localdapodiksync');

require_capability('local/dapodik:sync', context_system::instance());

$PAGE->set_url(new moodle_url('/local/dapodik/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_dapodik'));
$PAGE->set_heading(get_string('pluginname', 'local_dapodik'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_dapodik'));

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'sync' && confirm_sesskey()) {
    echo $OUTPUT->notification('Memulai proses sinkronisasi dengan Dapodik Kemendikdasmen...', 'info');

    try {
        $manager = new \local_dapodik\sync_manager();
        $stats = $manager->sync_all(function($msg) {
            echo html_writer::div(s($msg), 'alert alert-secondary');
            flush();
        });

        echo $OUTPUT->notification(get_string('sync_success', 'local_dapodik'), 'success');
        echo html_writer::tag('pre', json_encode($stats, JSON_PRETTY_PRINT));
    } catch (\Exception $e) {
        echo $OUTPUT->notification(get_string('sync_failed', 'local_dapodik', $e->getMessage()), 'error');
    }

    echo html_writer::link(new moodle_url('/local/dapodik/index.php'), 'Kembali ke Halaman Sinkronisasi', ['class' => 'btn btn-secondary mt-3']);
} else {
    echo html_writer::tag('p', 'Halaman ini memungkinkan Anda melakukan sinkronisasi instan data Siswa, Guru (GTK), Rombel (Cohort), dan Mata Pelajaran dari Dapodik ke LMS Moodle.');

    $syncurl = new moodle_url('/local/dapodik/index.php', ['action' => 'sync', 'sesskey' => sesskey()]);
    echo html_writer::link($syncurl, get_string('sync_now', 'local_dapodik'), ['class' => 'btn btn-primary btn-lg']);
}

echo $OUTPUT->footer();
