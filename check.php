<?php

require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $OUTPUT, $PAGE;

$submissionId = intval(optional_param('id', -1, PARAM_INT));

$url = new moodle_url('/local/sqljudge/check.php');
$url->param('id', $submissionId);
$PAGE->set_url($url);

$assign = $DB->get_record_sql(
    'SELECT a.course 
    FROM {assign_submission} s
    JOIN {assign} a
        ON s.assignment = a.id
    WHERE s.id = ?', array($submissionId));

require_login($assign->course, false, null, false, true);

$backendAddress = '127.0.0.1:5000'; //FIXME get_config('local_sqljudge', 'backendaddress');
$backendPort = explode(':', $backendAddress)[1];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $backendAddress . '/api/submission/check?submissionIds=' . $submissionId,
    CURLOPT_PORT => $backendPort,
    CURLOPT_HEADER => true
));
$resp = curl_exec($curl);

if ($resp === false) {
    // Error occurred during the request
    $error = curl_error($curl);
    curl_close($curl);
    echo "Error: " . $error;
}

$respcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

echo $OUTPUT->header();

if ($respcode === 200) {
    echo $OUTPUT->notification('Checked', 'notifyinfo');
} else if ($respcode === 422) {
    echo $OUTPUT->notification('Checking submission failed', 'notifyerror');
} else {
    echo $OUTPUT->notification('Error sending request, try again later', 'notifyerror');
}

echo $OUTPUT->footer();