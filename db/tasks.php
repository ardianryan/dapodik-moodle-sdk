<?php
// Scheduled tasks definitions for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_dapodik\task\sync_task',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '2', // Run every night at 02:00 AM.
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
];
