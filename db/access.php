<?php
// Capability definitions for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/dapodik:sync' => [
        'riskbitmask'  => RISK_DATALOSS | RISK_CONFIG,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
