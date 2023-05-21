<?php

define("SQLJ_STATUS_PENDING", 0);
define("SQLJ_STATUS_ACCEPTED", 1);
define("SQLJ_STATUS_WRONG_ANSWER", 2);
define("SQLJ_STATUS_BANNED_OR_REQUIRED_WORDS_CONTENT", 3);
define("SQLJ_STATUS_CONTAINS_RESTRICTED_FUNCTIONS", 4);
define("SQLJ_STATUS_TIME_LIMIT_EXCEEDED", 5);
define("SQLJ_STATUS_UNKNOWN_ERROR", 6);

function sqljudge_get_supported_dbms_list() {
    $backendAddress = '127.0.0.1:5000'; //FIXME get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $backendAddress . '/api/database/dbmslist',
        CURLOPT_PORT => $backendPort
    ));
    $resp = curl_exec($curl);

    if ($resp === false) {
        // Error occurred during the request
        $error = curl_error($curl);
        curl_close($curl);
        echo "Error: " . $error;
        return false;
    }

    curl_close($curl);
    
    $data = json_decode($resp, true);

    if ($data === null) {
        echo "Error: Failed to parse response.";
        return false;
    }

    $result = array();

    foreach ($data as $key => $value) {
        $result[$value] = $value;
    }

    return $result;
}
