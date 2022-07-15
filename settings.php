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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');

    $settings = new admin_settingpage('sqljudge', get_string('pluginname', 'local_sqljudge'));

    $settings->add(new admin_setting_configtext('local_sqljudge/backendaddress',
        get_string('backendaddress', 'local_sqljudge'),
        get_string('backendaddress_help', 'local_sqljudge'),
        '127.0.0.1:727',
        PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_sqljudge/syncinterval', 
        get_string('syncinterval', 'local_sqljudge'), 
        get_string('syncinterval_help', 'local_sqljudge'), 
        10, 
        PARAM_INT));
    $settings->add(new admin_setting_configtext('local_sqljudge/maxramlimit', 
        get_string('maxramlimit', 'local_sqljudge'), 
        get_string('maxramlimit_help', 'local_sqljudge'), 
        64, 
        PARAM_INT));
    $settings->add(new admin_setting_configtext('local_sqljudge/maxtimelimit', 
        get_string('maxtimelimit', 'local_sqljudge'), 
        get_string('maxtimelimit_help', 'local_sqljudge'), 
        20, 
        PARAM_INT));

    $choices = sqljudge_get_supported_dbms_list();
    $settings->add(new admin_setting_configmulticheckbox('local_sqljudge/availabledbms',
        get_string('availabledbms', 'local_sqljudge'),
        get_string('availabledbms_help', 'local_sqljudge'),
        $choices,
        $choices));

    $settings->add(new admin_setting_configselect('local_sqljudge/defaultdbms', 
        get_string('defaultdbms', 'local_sqljudge'), 
        get_string('defaultdbms_help', 'local_sqljudge'), 
        '', 
        $choices));
    $ADMIN->add('localplugins', $settings);
}