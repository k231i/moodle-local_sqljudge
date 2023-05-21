<?php

// if (!defined('ASSIGNMENT_SQLJUDGE_MAX_TIME') && !defined('ASSIGNMENT_SQLJUDGE_MAX_RAM')) {
//     define('ASSIGNMENT_SQLJUDGE_MAX_TIME', get_config('local_sqljudge', 'maxtimelimit'));
//     define('ASSIGNMENT_SQLJUDGE_MAX_RAM', 1024 * 1024 * get_config('local_sqljudge', 'maxramlimit'));
// }

// require_once($CFG->dirroot . '/mod/assign/feedback/sqljudge/lib.php');


// $settings->add(new admin_setting_heading('sqljudge_help', 
//     get_string('user_help_heading', 'assignfeedback_sqljudge'), 
//     get_string('user_help', 'assignfeedback_sqljudge'))
// );
// $settings->add(new admin_setting_configselect('assignment_sqlj_max_time', 
//     get_string('maxtime', 'assignfeedback_sqljudge'), 
//     get_string('maxtimehelp', 'assignfeedback_sqljudge'), 
//     ASSIGNMENT_SQLJUDGE_MAX_TIME, 
//     get_max_times())
// );
// $settings->add(new admin_setting_configselect('assignment_oj_max_mem', 
//     get_string('maxramusage', 'assignfeedback_sqljudge'), 
//     get_string('maxramusagehelp', 'assignfeedback_sqljudge'), 
//     ASSIGNMENT_SQLJUDGE_MAX_RAM, 
//     get_max_ram_usages())
// );