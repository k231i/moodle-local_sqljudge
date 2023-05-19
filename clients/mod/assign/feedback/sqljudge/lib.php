<?php

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