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

require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot . "/mod/assign/locallib.php");

function get_max_times() {
    $maxtime = get_config('local_sqljudge', 'maxtimelimit');
    for ($i = 5; $i < $maxtime; $i += 5)
        $time[$i] = get_string('numseconds', 'moodle', $i);
    $time[$maxtime] = get_string('numseconds', 'moodle', $maxtime);
    return $time;
}

function get_databases() {
    global $DB;
    $databases = $DB->get_records('database_sqlj', null, '', 'id, name, dbms, createdon');
    $result = array();
    foreach ($databases as $id => $db_object) {
        $result[id] = $db_object->dbms . ': ' . $db_object->name 
            . ' (' . date('Y-m-d H:m:s', $db_object->createdon) . ')';
    }
    return $result;
}

// function get_max_ram_usages() {
//     $maxsize = 1024 * 1024 * get_config('local_sqljudge', 'maxramlimit');
//     for ($i = 1024 * 1024; $i <= $maxsize; $i *= 2)
//         $ramusage[$i] = display_size($i);
//     return $ramusage;
// }