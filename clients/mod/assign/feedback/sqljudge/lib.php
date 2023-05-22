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
        $result[$id] = $db_object->dbms . ': ' . $db_object->name 
            . ' (' . date('Y-m-d H:m:s', $db_object->createdon) . ')';
    }
    return $result;
}

function add_instance(stdClass $sqljAssign, $assignId) {
    global $DB;

    $returnid = null;
    if ($assignId) {
        $sqlj_assignment = $sqljAssign;
        $sqlj_assignment->assignment = $assignId;
        $returnid = $DB->insert_record('assignment_sqlj', $sqlj_assignment);

        generate_correct_output($assignId);
    }

    return $returnid;
}

function update_instance($sqljAssign, $assignId) {
    global $DB;

    $returnid = null;

    if ($assignId) {
        $sqlj_assignment = $sqljAssign;
        $old_sqlj_assignment = $DB->get_record('assignment_sqlj', array('assignment' => $assignId));
        if ($old_sqlj_assignment) {
            $sqlj_assignment->id = $old_sqlj_assignment->id;
            $returnid = $DB->update_record('assignment_sqlj', $sqlj_assignment);

            generate_correct_output($assignId);
        }
    }

    return $returnid;
}

function generate_correct_output($assignId) {
    $backendAddress = '127.0.0.1:5000'; //FIXME get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $backendAddress . '/api/submission/correctoutput?submissionIds=' . $assignId,
        CURLOPT_PORT => $backendPort,
        CURLOPT_HEADER => true
    ));
    $resp = curl_exec($curl);
    
    if ($resp === false) {
        // Error occurred during the request
        $error = curl_error($curl);
        curl_close($curl);
        echo "Error: " . $error;
        exit();
    }
    
    $respcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($respcode !== 200) {
        echo get_string('checkerrorcode', 'assignfeedback_sqljudge', $respcode);
    }
}