<?php

namespace Smansage\DapodikMoodle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * HTTP Client to fetch data from Dapodik WebService (port 5774).
 */
class DapodikHttpClient
{
    protected string $baseUrl;
    protected string $npsn;
    protected string $token;
    protected GuzzleClient $client;

    public function __construct(string $npsn, string $token, string $host = '127.0.0.1', int $port = 5774, float $timeout = 30.0)
    {
        if (preg_match('/[\r\n]/', $npsn)) {
            throw new \InvalidArgumentException("NPSN must not contain newline characters");
        }
        if (preg_match('/[\r\n]/', $token)) {
            throw new \InvalidArgumentException("Token must not contain newline characters (CRLF injection prevention)");
        }

        $this->npsn = trim($npsn);
        $this->token = trim($token);

        $cleanHost = trim($host);
        if (!str_starts_with($cleanHost, 'http://') && !str_starts_with($cleanHost, 'https://')) {
            $cleanHost = 'http://' . $cleanHost;
        }
        $this->baseUrl = rtrim($cleanHost, '/') . ':' . $port . '/WebService';

        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl . '/',
            'timeout'  => $timeout,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json, text/plain, */*',
                'User-Agent'    => 'dapodik-moodle-bridge/1.0.0',
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $endpoint, array $params = []): array
    {
        $cleanEndpoint = ltrim($endpoint, '/');
        if (str_contains($cleanEndpoint, '..') || str_contains($cleanEndpoint, '\\')) {
            throw new \InvalidArgumentException("Invalid endpoint: path traversal detected");
        }

        $query = array_merge(['npsn' => $this->npsn], $params);
        $response = $this->client->get($cleanEndpoint, ['query' => $query]);

        $raw = (string) $response->getBody();
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [];
        }

        if (isset($decoded['rows'])) {
            if (is_array($decoded['rows'])) {
                // Check if associative single object (e.g. getSekolah).
                if (!empty($decoded['rows']) && array_keys($decoded['rows']) !== range(0, count($decoded['rows']) - 1)) {
                    return [$decoded['rows']];
                }
                return $decoded['rows'];
            }
        }

        return $decoded['data'] ?? $decoded;
    }

    public function getSekolah(): array
    {
        return $this->request('getSekolah');
    }

    public function getGtk(int $page = 1, int $limit = 100): array
    {
        return $this->request('getGtk', ['page' => $page, 'limit' => $limit]);
    }

    public function getPesertaDidik(int $page = 1, int $limit = 100): array
    {
        return $this->request('getPesertaDidik', ['page' => $page, 'limit' => $limit]);
    }

    public function getRombonganBelajar(?string $semesterId = null): array
    {
        $p = [];
        if ($semesterId) {
            $p['semester_id'] = $semesterId;
        }
        return $this->request('getRombonganBelajar', $p);
    }

    public function getPrasarana(int $page = 1, int $limit = 100): array
    {
        return $this->request('getPrasarana', ['page' => $page, 'limit' => $limit]);
    }
}
