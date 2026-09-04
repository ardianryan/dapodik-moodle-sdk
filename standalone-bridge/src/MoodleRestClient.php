<?php

namespace Smansage\DapodikMoodle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client for Moodle REST Web Services API.
 */
class MoodleRestClient
{
    protected string $serverUrl;
    protected string $wsToken;
    protected GuzzleClient $client;

    public function __construct(string $moodleUrl, string $wsToken, float $timeout = 30.0)
    {
        if (preg_match('/[\r\n]/', $wsToken)) {
            throw new \InvalidArgumentException("Token must not contain newline characters");
        }

        $cleanUrl = rtrim(trim($moodleUrl), '/');
        $this->serverUrl = $cleanUrl . '/webservice/rest/server.php';
        $this->wsToken = trim($wsToken);

        $this->client = new GuzzleClient([
            'timeout' => $timeout,
            'headers' => [
                'Accept'     => 'application/json',
                'User-Agent' => 'dapodik-moodle-bridge/1.0.0',
            ],
        ]);
    }

    /**
     * Call a Moodle WebService function.
     *
     * @throws GuzzleException
     */
    public function call(string $wsFunction, array $params = []): array
    {
        $payload = array_merge($params, [
            'wstoken'            => $this->wsToken,
            'wsfunction'         => $wsFunction,
            'moodlewsrestformat' => 'json',
        ]);

        $response = $this->client->post($this->serverUrl, [
            'form_params' => $payload,
        ]);

        $raw = (string) $response->getBody();
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [];
        }

        if (isset($decoded['exception'])) {
            throw new \RuntimeException("Moodle API Error [{$decoded['errorcode']}]: {$decoded['message']}");
        }

        return $decoded;
    }

    /**
     * Create users in Moodle (core_user_create_users).
     */
    public function createUsers(array $users): array
    {
        return $this->call('core_user_create_users', ['users' => $users]);
    }

    /**
     * Find user by field (e.g. username / idnumber).
     */
    public function getUsersByField(string $field, array $values): array
    {
        return $this->call('core_user_get_users_by_field', [
            'field'  => $field,
            'values' => $values,
        ]);
    }

    /**
     * Create cohorts in Moodle (core_cohort_create_cohorts).
     */
    public function createCohorts(array $cohorts): array
    {
        return $this->call('core_cohort_create_cohorts', ['cohorts' => $cohorts]);
    }

    /**
     * Add members to cohort (core_cohort_add_cohort_members).
     */
    public function addCohortMembers(array $members): array
    {
        return $this->call('core_cohort_add_cohort_members', ['members' => $members]);
    }

    /**
     * Create courses (core_course_create_courses).
     */
    public function createCourses(array $courses): array
    {
        return $this->call('core_course_create_courses', ['courses' => $courses]);
    }

    /**
     * Enrol users into course (enrol_manual_enrol_users).
     */
    public function enrolUsers(array $enrolments): array
    {
        return $this->call('enrol_manual_enrol_users', ['enrolments' => $enrolments]);
    }
}
