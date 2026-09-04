<?php
// Admin settings for local_dapodik.
//
// @package    local_dapodik
// @copyright  2026 Ryan Ardian <inisaya@ardianryan.com>, SMA Negeri 1 Gedeg (@smansagewithai)
// @license    MIT-NC

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_dapodik', get_string('pluginname', 'local_dapodik'));

    $settings->add(new admin_setting_heading('local_dapodik/settings_heading',
        get_string('settings_heading', 'local_dapodik'),
        get_string('settings_heading_desc', 'local_dapodik')
    ));

    $settings->add(new admin_setting_configtext('local_dapodik/host',
        get_string('host', 'local_dapodik'),
        get_string('host_desc', 'local_dapodik'),
        '127.0.0.1', PARAM_HOST
    ));

    $settings->add(new admin_setting_configtext('local_dapodik/port',
        get_string('port', 'local_dapodik'),
        get_string('port_desc', 'local_dapodik'),
        '5774', PARAM_INT
    ));

    $settings->add(new admin_setting_configtext('local_dapodik/npsn',
        get_string('npsn', 'local_dapodik'),
        get_string('npsn_desc', 'local_dapodik'),
        '', PARAM_ALPHANUM
    ));

    $settings->add(new admin_setting_configpasswordunmask('local_dapodik/token',
        get_string('token', 'local_dapodik'),
        get_string('token_desc', 'local_dapodik'),
        ''
    ));

    $settings->add(new admin_setting_configpasswordunmask('local_dapodik/default_password',
        get_string('default_password', 'local_dapodik'),
        get_string('default_password_desc', 'local_dapodik'),
        'Dapodik@2026!'
    ));

    $settings->add(new admin_setting_configtext('local_dapodik/email_domain',
        get_string('email_domain', 'local_dapodik'),
        get_string('email_domain_desc', 'local_dapodik'),
        'sekolah.sch.id', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configcheckbox('local_dapodik/sync_students',
        get_string('sync_students', 'local_dapodik'),
        get_string('sync_students_desc', 'local_dapodik'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox('local_dapodik/sync_teachers',
        get_string('sync_teachers', 'local_dapodik'),
        get_string('sync_teachers_desc', 'local_dapodik'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox('local_dapodik/sync_cohorts',
        get_string('sync_cohorts', 'local_dapodik'),
        get_string('sync_cohorts_desc', 'local_dapodik'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox('local_dapodik/sync_courses',
        get_string('sync_courses', 'local_dapodik'),
        get_string('sync_courses_desc', 'local_dapodik'),
        1
    ));

    $ADMIN->add('localplugins', $settings);
}
