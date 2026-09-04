<?php
// Scheduled task implementation for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

namespace local_dapodik\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Background scheduled task to automatically sync Dapodik data.
 */
class sync_task extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_sync', 'local_dapodik');
    }

    public function execute() {
        mtrace("Executing scheduled Dapodik synchronization...");

        try {
            $manager = new \local_dapodik\sync_manager();
            $manager->sync_all();
            mtrace("Dapodik scheduled sync completed.");
        } catch (\Exception $e) {
            mtrace("Error during Dapodik sync: " . $e->getMessage());
        }
    }
}
