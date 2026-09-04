<?php
// Bahasa Indonesia language pack for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Integrator Dapodik Kemendikdasmen';
$string['dapodik:sync'] = 'Menjalankan sinkronisasi data Dapodik';

// Settings.
$string['settings_heading'] = 'Pengaturan Koneksi WebService Dapodik';
$string['settings_heading_desc'] = 'Konfigurasi parameter koneksi ke server WebService Dapodik lokal (port 5774).';
$string['host'] = 'Host / IP Server Dapodik';
$string['host_desc'] = 'Alamat IP komputer yang menjalankan Dapodik Desktop (contoh: 127.0.0.1 atau 192.168.1.100).';
$string['port'] = 'Port Dapodik';
$string['port_desc'] = 'Port default WebService Dapodik adalah 5774.';
$string['npsn'] = 'NPSN Sekolah';
$string['npsn_desc'] = '8 digit Nomor Pokok Sekolah Nasional.';
$string['token'] = 'Token WebService';
$string['token_desc'] = 'Token Bearer yang dibuat di Pengaturan WebService Dapodik.';
$string['default_password'] = 'Password Default Pengguna';
$string['default_password_desc'] = 'Password awal untuk akun siswa dan guru baru (pengguna diwajibkan ganti password saat login pertama).';
$string['email_domain'] = 'Domain Email Default';
$string['email_domain_desc'] = 'Domain email untuk siswa jika email Dapodik kosong (contoh: sekolah.sch.id).';
$string['sync_students'] = 'Sinkronisasi Siswa (Peserta Didik)';
$string['sync_students_desc'] = 'Sinkronkan data siswa Dapodik ke tabel pengguna Moodle (username = NISN).';
$string['sync_teachers'] = 'Sinkronisasi Guru / GTK';
$string['sync_teachers_desc'] = 'Sinkronkan data Guru dan Tendik ke akun pengajar Moodle.';
$string['sync_cohorts'] = 'Sinkronisasi Cohort (Rombongan Belajar)';
$string['sync_cohorts_desc'] = 'Otomatis membuat Cohort kelas di Moodle berdasarkan Rombel Dapodik.';
$string['sync_courses'] = 'Sinkronisasi Mata Pelajaran & Enrolment';
$string['sync_courses_desc'] = 'Otomatis membuat Course mata pelajaran dan mendaftarkan siswa rombel ke course tersebut.';

// Tasks & UI.
$string['task_sync'] = 'Sinkronisasi Otomatis Dapodik Terjadwal';
$string['sync_now'] = 'Jalankan Sinkronisasi Sekarang';
$string['sync_success'] = 'Sinkronisasi Dapodik berhasil diselesaikan!';
$string['sync_failed'] = 'Sinkronisasi Dapodik mengalami kendala: {$a}';
