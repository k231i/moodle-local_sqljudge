<?php
// This file is part of Moodle - https://moodle.org
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

if (!defined('ASSIGNMENT_SQLJUDGE_MAX_TIME') && !defined('ASSIGNMENT_SQLJUDGE_MAX_RAM')) {
    define('ASSIGNMENT_SQLJUDGE_MAX_TIME', get_config('local_sqljudge', 'maxtimelimit'));
    define('ASSIGNMENT_SQLJUDGE_MAX_RAM', 1024 * 1024 * get_config('local_sqljudge', 'maxramlimit'));
}

require_once($CFG->dirroot . '/mod/assign/feedback/sqljudge/lib.php');


// $settings->add(new admin_setting_heading('sqljudge_help', 
//     get_string('user_help_heading', 'assignfeedback_sqljudge'), 
//     get_string('user_help', 'assignfeedback_sqljudge'))
// );
$settings->add(new admin_setting_configselect('assignment_oj_max_cpu', 
    get_string('maxtime', 'assignfeedback_sqljudge'), 
    get_string('maxtimehelp', 'assignfeedback_sqljudge'), 
    ASSIGNMENT_SQLJUDGE_MAX_TIME, 
    get_max_times())
);
$settings->add(new admin_setting_configselect('assignment_oj_max_mem', 
    get_string('maxramusage', 'assignfeedback_sqljudge'), 
    get_string('maxramusagehelp', 'assignfeedback_sqljudge'), 
    ASSIGNMENT_SQLJUDGE_MAX_RAM, 
    get_max_ram_usages())
);