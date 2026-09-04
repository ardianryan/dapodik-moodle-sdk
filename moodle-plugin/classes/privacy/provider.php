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
 * Privacy Subsystem implementation for local_dapodik.
 *
 * @package    local_dapodik
 * @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dapodik\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy provider for local_dapodik.
 */
class provider implements metadata_provider, \core_privacy\local\request\user_preference_provider {

    /**
     * Describe the personal data exported/stored by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        // This plugin connects to an external WebService (Dapodik) and populates core user tables.
        $collection->add_external_location_link(
            'dapodik_webservice',
            [
                'nisn'        => 'privacy:metadata:dapodik:nisn',
                'fullname'    => 'privacy:metadata:dapodik:fullname',
                'email'       => 'privacy:metadata:dapodik:email',
                'institution' => 'privacy:metadata:dapodik:institution',
            ],
            'privacy:metadata:dapodik'
        );

        return $collection;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The user ID.
     */
    public static function export_user_preferences(int $userid) {
        // No user preferences stored by local_dapodik.
    }
}
