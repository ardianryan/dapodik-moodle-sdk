<?php
// Interactive Dashboard for Modular Sync and Teacher Assignment.
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

$tab = optional_param('tab', 'sync', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);

echo $OUTPUT->header();
echo $OUTPUT->heading('🎓 Pusat Kendali Integrasi Dapodik Kemendikdasmen');

// Nav tabs.
echo '<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link ' . ($tab === 'sync' ? 'active' : '') . '" href="index.php?tab=sync">⚡ Penarikan Bertahap (Modular Sync)</a>
  </li>
  <li class="nav-item">
    <a class="nav-link ' . ($tab === 'mapping' ? 'active' : '') . '" href="index.php?tab=mapping">👨‍🏫 Pemetaan Pengajar (Teacher Assignment)</a>
  </li>
</ul>';

$manager = new \local_dapodik\sync_manager();

// =============================================================================
// TAB 1: PENARIKAN BERTAHAP (MODULAR SYNC)
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

        echo $OUTPUT->notification('Memulai proses penarikan data sesuai pilihan Anda...', 'info');

        try {
            $stats = $manager->sync_modular($options, function($msg) {
                echo html_writer::div(s($msg), 'alert alert-secondary py-1 px-2 my-1');
                flush();
            });

            echo $OUTPUT->notification('Sinkronisasi data pilihan berhasil diselesaikan!', 'success');
            echo html_writer::tag('pre', json_encode($stats, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            echo $OUTPUT->notification('Terjadi kendala saat sinkronisasi: ' . $e->getMessage(), 'error');
        }

        echo html_writer::link(new moodle_url('/local/dapodik/index.php?tab=sync'), 'Kembali', ['class' => 'btn btn-secondary mt-3']);
    } else {
        echo '<div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title">Pilih Entitas yang Ingin Ditarik dari Dapodik</h5>
                <p class="text-muted">Anda memiliki keleluasaan penuh untuk menarik data secara terpisah (misal: rombel & mapel saja, siswa saja, atau guru saja).</p>
                <form method="post" action="index.php?tab=sync&action=dosync">
                    <input type="hidden" name="sesskey" value="' . sesskey() . '">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_students" value="1" id="chkStudents" checked>
                        <label class="form-check-label" for="chkStudents">
                            <strong>Peserta Didik (Siswa)</strong> - Otomatis buat akun siswa (Username = NISN).
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_teachers" value="1" id="chkTeachers" checked>
                        <label class="form-check-label" for="chkTeachers">
                            <strong>Guru & Tendik (GTK)</strong> - Otomatis buat akun guru (Username = NIP / NIK).
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_cohorts" value="1" id="chkCohorts" checked>
                        <label class="form-check-label" for="chkCohorts">
                            <strong>Rombongan Belajar (Rombel / Cohorts)</strong> - Buat kelompok Cohort kelas & enrol siswa rombel.
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="sync_courses" value="1" id="chkCourses" checked>
                        <label class="form-check-label" for="chkCourses">
                            <strong>Mata Pelajaran (Courses)</strong> - Buat Course per Mapel per Rombel.
                        </label>
                    </div>
                    <div class="form-check mb-4 ms-4 border-start ps-3 py-1 bg-light rounded">
                        <input class="form-check-input" type="checkbox" name="auto_teacher" value="1" id="chkAutoTeacher">
                        <label class="form-check-label" for="chkAutoTeacher">
                            <em>Otomatis Daftarkan Guru Pengampu dari Dapodik ke Course</em><br />
                            <small class="text-muted">Centang jika ingin guru langsung di-assign otomatis. Biarkan kosong jika ingin membagi / memilih guru secara manual di tab <strong>Pemetaan Pengajar</strong>.</small>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">🚀 Jalankan Penarikan Terpilih</button>
                </form>
            </div>
        </div>';
    }
}

// =============================================================================
// TAB 2: PEMETAAN PENGAJAR (TEACHER ASSIGNMENT)
// =============================================================================
if ($tab === 'mapping') {
    // Handle manual assignment action.
    if ($action === 'assign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->assign_teacher_to_course($courseid, $teacherid)) {
            echo $OUTPUT->notification('Guru pengajar berhasil didaftarkan ke mata pelajaran!', 'success');
        } else {
            echo $OUTPUT->notification('Gagal mendaftarkan guru ke kursus.', 'error');
        }
    }

    // Handle manual unassignment.
    if ($action === 'unassign' && confirm_sesskey()) {
        $courseid = required_param('courseid', PARAM_INT);
        $teacherid = required_param('teacherid', PARAM_INT);

        if ($manager->unassign_teacher_from_course($courseid, $teacherid)) {
            echo $OUTPUT->notification('Guru pengajar berhasil dilepas dari mata pelajaran.', 'info');
        }
    }

    $dapodikCourses = $manager->get_dapodik_courses_list();
    $availableTeachers = $manager->get_available_teachers();

    echo '<div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">Pembagian Tugas & Pemetaan Guru Pengajar</h5>
            <p class="text-muted mb-0">Atur guru pengajar untuk setiap kelas. Sangat berguna jika guru di Dapodik belum lengkap atau diajar oleh guru honorer/pengganti lain.</p>
        </div>
        <span class="badge bg-primary fs-6">' . count($dapodikCourses) . ' Kursus Dapodik</span>
    </div>';

    if (empty($dapodikCourses)) {
        echo $OUTPUT->notification('Belum ada Course Dapodik yang ditarik. Silakan lakukan penarikan Kursus di tab <strong>Penarikan Bertahap</strong> terlebih dahulu.', 'warning');
    } else {
        echo '<div class="table-responsive">
            <table class="table table-hover table-bordered align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 30%;">Mata Pelajaran & Kelas (Course)</th>
                        <th style="width: 20%;">Guru di Dapodik</th>
                        <th style="width: 25%;">Guru Pengajar di Moodle Saat Ini</th>
                        <th style="width: 25%;">Aksi Assign Guru (PNS / P3K / Honorer)</th>
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
                echo '<span class="badge bg-warning text-dark">Belum ada pengajar</span>';
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
                        <a href="' . $unassignUrl . '" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus Pengajar">&times;</a>
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
                        <option value="">-- Pilih Guru Moodle --</option>';
            foreach ($availableTeachers as $at) {
                echo '<option value="' . $at->id . '">' . s($at->firstname . ' ' . $at->lastname) . ' (' . s($at->username) . ')</option>';
            }
            echo '    </select>
                    <button type="submit" class="btn btn-sm btn-success text-nowrap">➕ Assign</button>
                </form>
            </td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}

echo $OUTPUT->footer();
