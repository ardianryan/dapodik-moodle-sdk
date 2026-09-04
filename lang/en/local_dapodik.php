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
 * English language pack for local_dapodik.
 *
 * @package    local_dapodik
 * @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Dapodik Kemendikdasmen integrator';
$string['dapodik:sync'] = 'Trigger Dapodik synchronization';

// Settings.
$string['settings_heading'] = 'Dapodik WebService connection settings';
$string['settings_heading_desc'] = 'Configure connection parameters to local Dapodik WebService (port 5774).';
$string['host'] = 'Dapodik server host or IP';
$string['host_desc'] = 'IP address of computer running Dapodik desktop (e.g. 127.0.0.1 or 192.168.1.100).';
$string['port'] = 'Dapodik port';
$string['port_desc'] = 'Default WebService port is 5774.';
$string['npsn'] = 'School NPSN';
$string['npsn_desc'] = '8-digit national school identification number.';
$string['token'] = 'WebService token';
$string['token_desc'] = 'Bearer token generated from Dapodik desktop settings.';
$string['default_password'] = 'Default user password';
$string['default_password_desc'] = 'Default initial password for newly created student and teacher accounts.';
$string['email_domain'] = 'Default email domain';
$string['email_domain_desc'] = 'Domain used for student emails if Dapodik email is missing (e.g. sekolah.sch.id).';
$string['sync_students'] = 'Sync students';
$string['sync_students_desc'] = 'Synchronize Dapodik students into Moodle users.';
$string['sync_teachers'] = 'Sync teachers and staff';
$string['sync_teachers_desc'] = 'Synchronize Dapodik teachers and staff into Moodle users.';
$string['sync_cohorts'] = 'Sync cohorts';
$string['sync_cohorts_desc'] = 'Automatically create Moodle cohorts matching Dapodik study groups.';
$string['sync_courses'] = 'Sync courses and enrolments';
$string['sync_courses_desc'] = 'Automatically create courses from Dapodik learning subjects and enroll students.';

// UI Tabs.
$string['control_center_title'] = 'Dapodik Kemendikdasmen control center';
$string['tab_sync'] = 'Modular synchronization';
$string['tab_mapping'] = 'Teacher assignment';
$string['modular_sync_heading'] = 'Select entities to synchronize';
$string['modular_sync_desc'] = 'You have full control to selectively pull students, teachers, study groups, or course subjects.';
$string['opt_students'] = 'Students (Peserta Didik) - creates student accounts with username set to NISN.';
$string['opt_teachers'] = 'Teachers and staff (GTK) - creates teacher accounts with username set to NIP or NIK.';
$string['opt_cohorts'] = 'Study groups (Rombel / Cohorts) - creates cohort groups and adds member students.';
$string['opt_courses'] = 'Course subjects - creates course per subject per study group.';
$string['opt_auto_teacher'] = 'Automatically enrol teacher assigned in Dapodik to the course';
$string['opt_auto_teacher_desc'] = 'Enable if you want the Dapodik teacher enrolled immediately. Leave unchecked if assigning teachers manually.';
$string['btn_run_sync'] = 'Run selected synchronization';
$string['sync_started'] = 'Starting synchronization of selected items...';
$string['sync_success'] = 'Dapodik synchronization completed successfully!';
$string['sync_failed'] = 'Dapodik synchronization encountered an error: {$a}';
$string['btn_back'] = 'Back';

// Mapping Tab.
$string['mapping_heading'] = 'Teacher assignment and workload allocation';
$string['mapping_desc'] = 'Allocate teaching teachers to each course. Useful when teachers are not yet recorded in Dapodik or taught by substitute teachers.';
$string['badge_courses_count'] = '{$a} Dapodik courses';
$string['no_courses_yet'] = 'No Dapodik courses found yet. Please run course synchronization on the Modular synchronization tab first.';
$string['th_course'] = 'Subject and class (Course)';
$string['th_guru_dapodik'] = 'Teacher in Dapodik';
$string['th_guru_moodle'] = 'Current assigned teacher';
$string['th_action'] = 'Assign teacher action';
$string['no_teacher_assigned'] = 'No teacher assigned';
$string['select_teacher_placeholder'] = '-- Select teacher --';
$string['btn_assign'] = 'Assign';
$string['btn_unassign_title'] = 'Remove teacher';
$string['msg_assign_success'] = 'Teacher successfully assigned to course!';
$string['msg_assign_error'] = 'Failed to assign teacher to course.';
$string['msg_unassign_success'] = 'Teacher successfully unassigned from course.';

// Task.
$string['task_sync'] = 'Dapodik scheduled background synchronization';

// Privacy API Metadata.
$string['privacy:metadata:dapodik'] = 'Integrator Dapodik transmits school identification to local Dapodik WebService to retrieve educational rosters.';
$string['privacy:metadata:dapodik:nisn'] = 'The national student identifier (NISN) used as Moodle username.';
$string['privacy:metadata:dapodik:fullname'] = 'The student or teacher full name.';
$string['privacy:metadata:dapodik:email'] = 'The email address associated with student or teacher.';
$string['privacy:metadata:dapodik:institution'] = 'The school institution identifier.';
