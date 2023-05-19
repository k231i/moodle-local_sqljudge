<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    //require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');

    $settings = new admin_settingpage('sqljudge', get_string('pluginname', 'local_sqljudge'));

    $ADMIN->add( 'localplugins', $settings );

    $settings->add(new admin_setting_configtext('local_sqljudge/backendaddress',
        get_string('backendaddress', 'local_sqljudge'),
        get_string('backendaddress_help', 'local_sqljudge'),
        '127.0.0.1:5000',
        PARAM_TEXT));
    // $settings->add(new admin_setting_configtext('local_sqljudge/syncinterval', 
    //     get_string('syncinterval', 'local_sqljudge'), 
    //     get_string('syncinterval_help', 'local_sqljudge'), 
    //     10, 
    //     PARAM_INT));
    // $settings->add(new admin_setting_configtext('local_sqljudge/maxramlimit', 
    //     get_string('maxramlimit', 'local_sqljudge'), 
    //     get_string('maxramlimit_help', 'local_sqljudge'), 
    //     64, 
    //     PARAM_INT));
    // $settings->add(new admin_setting_configtext('local_sqljudge/maxtimelimit', 
    //     get_string('maxtimelimit', 'local_sqljudge'), 
    //     get_string('maxtimelimit_help', 'local_sqljudge'), 
    //     20, 
    //     PARAM_INT));

    // $choices = sqljudge_get_supported_dbms_list();

    // if ($choices !== false) {
    //     $settings->add(new admin_setting_configmulticheckbox('local_sqljudge/availabledbms',
    //         get_string('availabledbms', 'local_sqljudge'),
    //         get_string('availabledbms_help', 'local_sqljudge'),
    //         $choices,
    //         $choices));

    //     $settings->add(new admin_setting_configselect('local_sqljudge/defaultdbms', 
    //         get_string('defaultdbms', 'local_sqljudge'), 
    //         get_string('defaultdbms_help', 'local_sqljudge'), 
    //         '', 
    //         $choices));
    // }
    $ADMIN->add('localplugins', $settings);
}