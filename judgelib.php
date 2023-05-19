<?php

function sqljudge_get_supported_dbms_list() {
    $backendAddress = explode(':', get_config('local_sqljudge', 'backendaddress'));
    $backendHost = $backendAddress[0];
    $backendPort = $backendAddress[1];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $backendHost . '/api/database/dbmslist',
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
    echo $resp;
    $data = json_decode($resp, true);

    if ($data === null) {
        echo "Error: Failed to parse response.";
        return false;
    }

    return $data;
}
