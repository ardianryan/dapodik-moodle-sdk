# Changelog

Semua perubahan penting pada paket **`dapodik-moodle-sdk`** akan didokumentasikan di file ini.

Format changelog ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/), dan proyek ini mematuhi [Semantic Versioning](https://semver.org/lang/id/).

---

## [1.0.0] - 2026-09-04

### Ditambahkan
- **Native Moodle Plugin (`moodle-plugin/` / `local_dapodik`)**:
  - Dukungan Moodle 4.0, 4.5, dan 5.0+.
  - Panel konfigurasi koneksi WebService Dapodik di Site Administration.
  - Sinkronisasi otomatis terjadwal via Moodle Scheduled Task (Cron harian).
  - Halaman antarmuka web interaktif (`index.php`) untuk sinkronisasi manual instan.
  - Pemetaan entitas cerdas: Siswa ➔ User (NISN), Guru ➔ User (NIP/NIK), Rombel ➔ Cohort, Pembelajaran ➔ Course + Auto-enrol.
- **Standalone CLI Bridge (`standalone-bridge/`)**:
  - Executable CLI `bin/dapodik-moodle` untuk sinkronisasi via terminal/cron server tanpa modifikasi core Moodle.
  - `MoodleRestClient` untuk integrasi via Moodle Core WebService REST API.
  - `DapodikHttpClient` dengan normalisasi data dan proteksi keamanan tingkat tinggi.
  - Pengujian unit PHPUnit dengan kelulusan 100%.
- **Hardened Security**:
  - Proteksi anti-CRLF Injection pada token dan parameter query.
  - Proteksi Path Traversal pada endpoint.
  - Kepatuhan penuh UU Perlindungan Data Pribadi (UU PDP No. 27/2022).
- **Lisensi**:
  - MIT-NC (100% Gratis untuk Pendidikan, Dilarang untuk Tujuan Komersial).
