<?php

namespace Smansage\DapodikMoodle;

/**
 * Orchestrator bridging data from Dapodik to Moodle REST API.
 */
class DapodikMoodleBridge
{
    protected DapodikHttpClient $dapodik;
    protected MoodleRestClient $moodle;
    protected string $defaultPassword;
    protected string $emailDomain;

    public function __construct(
        DapodikHttpClient $dapodik,
        MoodleRestClient $moodle,
        string $defaultPassword = 'Dapodik@2026!',
        string $emailDomain = 'sekolah.sch.id'
    ) {
        $this->dapodik = $dapodik;
        $this->moodle = $moodle;
        $this->defaultPassword = $defaultPassword;
        $this->emailDomain = $emailDomain;
    }

    /**
     * Run full synchronization across students, teachers, and cohorts.
     */
    public function syncAll(?callable $logger = null): array
    {
        $log = $logger ?? function(string $msg) { echo "[Dapodik-Moodle] $msg\n"; };

        $stats = [
            'students' => 0,
            'teachers' => 0,
            'cohorts'  => 0,
        ];

        $log("Starting Dapodik to Moodle synchronization...");

        $log("Fetching and syncing students (NISN)...");
        $stats['students'] = $this->syncStudents($log);

        $log("Fetching and syncing teachers (GTK)...");
        $stats['teachers'] = $this->syncTeachers($log);

        $log("Fetching and syncing study groups (Cohorts)...");
        $stats['cohorts'] = $this->syncCohorts($log);

        $log("Sync completed: " . json_encode($stats));
        return $stats;
    }

    public function syncStudents(?callable $log = null): int
    {
        $count = 0;
        $page = 1;
        $limit = 100;

        while (true) {
            $rows = $this->dapodik->getPesertaDidik($page, $limit);
            if (empty($rows)) {
                break;
            }

            $usersToCreate = [];
            foreach ($rows as $pd) {
                $nisn = trim($pd['nisn'] ?? '');
                if (empty($nisn)) continue;

                $username = strtolower($nisn);
                $fullname = trim($pd['nama'] ?? 'Siswa ' . $nisn);
                $parts = explode(' ', $fullname, 2);

                $email = trim($pd['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email = $username . '@' . $this->emailDomain;
                }

                $usersToCreate[] = [
                    'username'    => $username,
                    'password'    => $this->defaultPassword,
                    'firstname'   => $parts[0],
                    'lastname'    => $parts[1] ?? '.',
                    'email'       => $email,
                    'idnumber'    => $nisn,
                    'institution' => $pd['sekolah_id'] ?? '',
                    'department'  => 'Peserta Didik',
                    'lang'        => 'id',
                ];
            }

            if (!empty($usersToCreate)) {
                try {
                    $this->moodle->createUsers($usersToCreate);
                    $count += count($usersToCreate);
                } catch (\Exception $e) {
                    if ($log) $log("Notice during student batch: " . $e->getMessage());
                }
            }

            if (count($rows) < $limit) {
                break;
            }
            $page++;
        }

        return $count;
    }

    public function syncTeachers(?callable $log = null): int
    {
        $count = 0;
        $page = 1;
        $limit = 100;

        while (true) {
            $rows = $this->dapodik->getGtk($page, $limit);
            if (empty($rows)) {
                break;
            }

            $usersToCreate = [];
            foreach ($rows as $gtk) {
                $nip = trim($gtk['nip'] ?? '');
                $nik = trim($gtk['nik'] ?? '');
                $username = !empty($nip) ? $nip : (!empty($nik) ? $nik : 'gtk_' . ($gtk['ptk_id'] ?? ''));
                if (empty($username)) continue;

                $username = strtolower($username);
                $fullname = trim($gtk['nama'] ?? 'Guru ' . $username);
                $parts = explode(' ', $fullname, 2);

                $email = trim($gtk['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email = $username . '@' . $this->emailDomain;
                }

                $usersToCreate[] = [
                    'username'    => $username,
                    'password'    => $this->defaultPassword,
                    'firstname'   => $parts[0],
                    'lastname'    => $parts[1] ?? '.',
                    'email'       => $email,
                    'idnumber'    => $gtk['ptk_id'] ?? $username,
                    'department'  => 'Guru / GTK',
                    'lang'        => 'id',
                ];
            }

            if (!empty($usersToCreate)) {
                try {
                    $this->moodle->createUsers($usersToCreate);
                    $count += count($usersToCreate);
                } catch (\Exception $e) {
                    if ($log) $log("Notice during teacher batch: " . $e->getMessage());
                }
            }

            if (count($rows) < $limit) {
                break;
            }
            $page++;
        }

        return $count;
    }

    public function syncCohorts(?callable $log = null): int
    {
        $count = 0;
        $rombels = $this->dapodik->getRombonganBelajar();
        if (empty($rombels)) {
            return 0;
        }

        $cohortsToCreate = [];
        foreach ($rombels as $rombel) {
            $nama = trim($rombel['nama'] ?? '');
            $rombelId = $rombel['rombongan_belajar_id'] ?? '';
            if (empty($nama)) continue;

            $cohortsToCreate[] = [
                'name'        => $nama,
                'idnumber'    => 'ROMBEL_' . $rombelId,
                'description' => 'Cohort Dapodik: ' . $nama,
                'categorytype' => [
                    'type'  => 'system',
                    'value' => '',
                ],
            ];
            $count++;
        }

        if (!empty($cohortsToCreate)) {
            try {
                $this->moodle->createCohorts($cohortsToCreate);
            } catch (\Exception $e) {
                if ($log) $log("Notice during cohort creation: " . $e->getMessage());
            }
        }

        return $count;
    }
}
