<?php

require_once(dirname(__FILE__) . '/../../config.php');

global $OUTPUT;

$submissionId = optional_param('id', -1, PARAM_INT);

$backendAddress = '127.0.0.1:5000'; //FIXME get_config('local_sqljudge', 'backendaddress');
$backendPort = explode(':', $backendAddress)[1];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $backendAddress . '/api/submission/checks/' . @$submissionId,
    CURLOPT_PORT => $backendPort,
    CURLOPT_HEADER => true
));
$resp = curl_exec($curl);

if ($resp === false) {
    // Error occurred during the request
    $error = curl_error($curl);
    curl_close($curl);
    echo "Error: " . $error;
    return false;
}

$respcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

if ($respcode === 200) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Checked', 'notifyinfo');
    echo $OUTPUT->footer();
} else if ($respcode === 422) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Checking submission failed', 'notifyerror');
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Error sending request, try again later', 'notifyerror');
    echo $OUTPUT->footer();
}