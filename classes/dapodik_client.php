<?php
// Dapodik WebService Client for Moodle.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

namespace local_dapodik;

defined('MOODLE_INTERNAL') || die();

/**
 * Hardened HTTP Client to communicate with Dapodik WebService (port 5774).
 */
class dapodik_client {
    protected string $baseurl;
    protected string $npsn;
    protected string $token;
    protected int $timeout;

    /**
     * Constructor using Moodle config or explicit arguments.
     */
    public function __construct(?string $host = null, ?int $port = null, ?string $npsn = null, ?string $token = null) {
        $host = $host ?? get_config('local_dapodik', 'host') ?: '127.0.0.1';
        $port = $port ?? (int)(get_config('local_dapodik', 'port') ?: 5774);
        $this->npsn = trim($npsn ?? get_config('local_dapodik', 'npsn') ?: '');
        $this->token = trim($token ?? get_config('local_dapodik', 'token') ?: '');
        $this->timeout = 30;

        // Hardened security checks.
        if (preg_match('/[\r\n]/', $this->npsn)) {
            throw new \moodle_exception('NPSN must not contain newline characters');
        }
        if (preg_match('/[\r\n]/', $this->token)) {
            throw new \moodle_exception('Token must not contain newline characters (CRLF injection prevention)');
        }

        $cleanhost = trim($host);
        if (!str_starts_with($cleanhost, 'http://') && !str_starts_with($cleanhost, 'https://')) {
            $cleanhost = 'http://' . $cleanhost;
        }
        $this->baseurl = rtrim($cleanhost, '/') . ':' . $port . '/WebService';
    }

    /**
     * Send HTTP GET request to Dapodik WebService.
     */
    public function request(string $endpoint, array $params = []): array {
        $cleanendpoint = ltrim($endpoint, '/');
        if (str_contains($cleanendpoint, '..') || str_contains($cleanendpoint, '\\')) {
            throw new \moodle_exception('Invalid endpoint: path traversal detected');
        }

        $query = array_merge(['npsn' => $this->npsn], $params);
        $url = $this->baseurl . '/' . $cleanendpoint . '?' . http_build_query($query);

        $curl = new \curl(['timeout' => $this->timeout]);
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Accept: application/json, text/plain, */*',
            'User-Agent: local_dapodik_moodle/1.0.0',
        ];
        $curl->setHeader($headers);

        $raw = $curl->get($url);
        $info = $curl->get_info();
        $httpcode = (int)($info['http_code'] ?? 0);

        if ($httpcode === 401 || $httpcode === 403) {
            throw new \moodle_exception("Dapodik authentication failed ($httpcode). Verify token and client IP whitelist in Dapodik.");
        }

        if ($httpcode < 200 || $httpcode >= 300) {
            throw new \moodle_exception("Dapodik HTTP Error $httpcode: " . substr((string)$raw, 0, 200));
        }

        $decoded = json_decode((string)$raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception("Invalid JSON response received from Dapodik: " . substr((string)$raw, 0, 200));
        }

        // Normalize rows format.
        $rows = [];
        if (is_array($decoded)) {
            if (isset($decoded['rows'])) {
                if (is_array($decoded['rows'])) {
                    // Check if associative single object (e.g. getSekolah).
                    if (!empty($decoded['rows']) && array_keys($decoded['rows']) !== range(0, count($decoded['rows']) - 1)) {
                        $rows = [$decoded['rows']];
                    } else {
                        $rows = $decoded['rows'];
                    }
                }
            } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
                $rows = $decoded['data'];
            } else {
                $rows = $decoded;
            }
        }

        return $rows;
    }

    public function get_sekolah(): array {
        return $this->request('getSekolah');
    }

    public function get_gtk(int $page = 1, int $limit = 100): array {
        return $this->request('getGtk', ['page' => $page, 'limit' => $limit]);
    }

    public function get_peserta_didik(int $page = 1, int $limit = 100): array {
        return $this->request('getPesertaDidik', ['page' => $page, 'limit' => $limit]);
    }

    public function get_rombongan_belajar(?string $semesterid = null): array {
        $p = [];
        if ($semesterid) {
            $p['semester_id'] = $semesterid;
        }
        return $this->request('getRombonganBelajar', $p);
    }

    public function get_prasarana(int $page = 1, int $limit = 100): array {
        return $this->request('getPrasarana', ['page' => $page, 'limit' => $limit]);
    }
}
