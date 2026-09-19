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
 * Bahasa Indonesia language pack for local_dapodik.
 *
 * @package    local_dapodik
 * @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$string['control_center_title'] = 'Pusat Kontrol Sinkronisasi Dapodik';
$string['tab_sync'] = 'Sinkronisasi Modular';
$string['tab_mapping'] = 'Pemetaan Guru ke Mapel';
$string['modular_sync_heading'] = 'Pilih Entitas Data untuk Disinkronkan';
$string['modular_sync_desc'] = 'Pilih data apa saja yang ingin diperbarui dari WebService Dapodik ke Moodle:';
$string['opt_students'] = 'Impor/perbarui akun siswa berdasarkan NISN';
$string['opt_teachers'] = 'Impor/perbarui akun guru/tendik berdasarkan NIP/NIK';
$string['opt_cohorts'] = 'Buat cohort rombel dan tambahkan anggota rombel';
$string['opt_courses'] = 'Buat course mapel pembelajaran dan tautkan cohort';
$string['opt_auto_teacher'] = 'Otomatis daftarkan guru yang terdata di Dapodik';
$string['btn_sync'] = 'Mulai Sinkronisasi Sekarang';
$string['btn_back'] = 'Kembali ke Pusat Kontrol';
$string['sync_started'] = 'Memulai proses sinkronisasi... Mohon tunggu.';
$string['mapping_heading'] = 'Daftar Kursus Pembelajaran & Pemetaan Pengajar';
$string['mapping_desc'] = 'Tinjau mata pelajaran pembelajaran yang telah disinkronkan dari Dapodik dan tetapkan guru pengampu:';
$string['col_course'] = 'Mata Pelajaran / Kelas';
$string['col_guru_dapodik'] = 'Guru di Dapodik';
$string['col_guru_moodle'] = 'Pengajar Moodle Terdaftar';
$string['col_action'] = 'Aksi / Kelola';
$string['no_teacher_assigned'] = 'Belum Ada Guru';
$string['btn_assign'] = 'Tetapkan Guru';
$string['select_teacher'] = '-- Pilih Guru --';
$string['no_courses_yet'] = 'Belum ada kursus Dapodik yang disinkronkan. Silakan jalankan sinkronisasi kursus pada tab Sinkronisasi Modular.';
$string['teacher_assigned'] = 'Guru berhasil ditetapkan ke kursus!';
$string['teacher_unassigned'] = 'Guru berhasil dilepas dari kursus!';
