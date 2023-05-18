<?php













function sqljudge_get_supported_dbms_list() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => get_config('local_sqljudge', 'backendaddress') . '/api/database/DbmsList'
    ));
    $resp = curl_exec($curl);
    print_r($resp); //FIXME
    curl_close($curl);
    return $resp;


    // TODO: actually HTTP GET the list from backend
    return ['PostgreSQL', 'MSSQL'];
}