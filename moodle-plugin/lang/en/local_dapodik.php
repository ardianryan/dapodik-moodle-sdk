<?php
// English language pack for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Dapodik Kemendikdasmen Integrator';
$string['dapodik:sync'] = 'Trigger Dapodik synchronization';

// Settings.
$string['settings_heading'] = 'Dapodik WebService Connection Settings';
$string['settings_heading_desc'] = 'Configure connection parameters to local Dapodik WebService (port 5774).';
$string['host'] = 'Dapodik Server Host / IP';
$string['host_desc'] = 'IP address of computer running Dapodik Desktop (e.g. 127.0.0.1 or 192.168.1.100).';
$string['port'] = 'Dapodik Port';
$string['port_desc'] = 'Default WebService port is 5774.';
$string['npsn'] = 'School NPSN';
$string['npsn_desc'] = '8-digit National School Identification Number.';
$string['token'] = 'WebService Token';
$string['token_desc'] = 'Bearer token generated from Dapodik Desktop Settings.';
$string['default_password'] = 'Default User Password';
$string['default_password_desc'] = 'Default initial password for newly created student and teacher accounts.';
$string['email_domain'] = 'Default Email Domain';
$string['email_domain_desc'] = 'Domain used for student emails if Dapodik email is missing (e.g. sekolah.sch.id).';
$string['sync_students'] = 'Sync Students';
$string['sync_students_desc'] = 'Synchronize Dapodik students into Moodle users.';
$string['sync_teachers'] = 'Sync Teachers / GTK';
$string['sync_teachers_desc'] = 'Synchronize Dapodik teachers and staff into Moodle users.';
$string['sync_cohorts'] = 'Sync Cohorts (Rombel)';
$string['sync_cohorts_desc'] = 'Automatically create Moodle cohorts matching Dapodik study groups.';
$string['sync_courses'] = 'Sync Courses & Enrolments';
$string['sync_courses_desc'] = 'Automatically create courses from Dapodik learning subjects and enroll students.';

// Tasks & UI.
$string['task_sync'] = 'Dapodik Scheduled Background Synchronization';
$string['sync_now'] = 'Run Synchronization Now';
$string['sync_success'] = 'Dapodik synchronization completed successfully!';
$string['sync_failed'] = 'Dapodik synchronization encountered an error: {$a}';
