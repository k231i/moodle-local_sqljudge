<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');


class sqljudge_dbadd_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', 'Add Database');

        $mform->addElement('text', 'name', 'Name:');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'description', 'Description:');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('select', 'dbms', 'DBMS:', 
            sqljudge_get_supported_dbms_list());
        $mform->setType('dbms', PARAM_ALPHA);

        $mform->addElement('textarea', 'dbcreationscript', 'DB Creation Script:');
        $mform->setType('dbcreationscript', PARAM_TEXT);

        $this->add_action_buttons(true, 'Submit');
    }
}

class sqljudge_dbcreate_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', 'Select Database');

        $databases = $DB->get_records('database_sqlj');
        if (!empty($databases)) {
            $options = array();
            foreach ($databases as $database) {
                $options[$database->id] = $database->dbms . ': ' . $database->name 
                    . ' (' . date('Y-m-d H:m:s', $database->createdon) . ')';
            }
            $mform->addElement('select', 'databaseid', 'Select Database:', $options);
            $mform->setType('databaseid', PARAM_INT);
            $mform->setDefault('databaseid', reset($databases)->id);
        }

        $createButtons = array();
        $createButtons[] = &$mform->createElement('submit', 'create', 'Create');
        $createButtons[] = &$mform->createElement('submit', 'forcecreate', 'Force Create');
        $mform->addGroup($createButtons, 'submitButtons', '', array(' '), false);

        $dropButtons = array();
        $dropButtons[] = &$mform->createElement('submit', 'drop', 'Drop');
        $dropButtons[] = &$mform->createElement('submit', 'dropdelete', 'Drop & Delete');
        $mform->addGroup($dropButtons, 'submitButtons', '', array(' '), false);
    }
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/sqljudge/index.php');
$PAGE->set_title(get_string('pluginname', 'local_sqljudge'));
$PAGE->set_heading("$SITE->shortname: " . get_string('pluginname', 'local_sqljudge'));

$output = $PAGE->get_renderer('local_sqljudge');

/// Output starts here
echo $output->header();

/// About
echo $output->heading(get_string('about', 'local_sqljudge'), 1);
echo $output->container(get_string('aboutcontent', 'local_sqljudge'), 'box copyright');

// Process form submission
$dbcreate_form = new sqljudge_dbcreate_form();
$dbadd_form = new sqljudge_dbadd_form();

if ($dbcreatedata = $dbcreate_form->get_data()) {
    $backendAddress = '127.0.0.1:5000'; //FIXME get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];

    $curl = curl_init();

    if (!empty($dbcreatedata->create)) {
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $backendAddress . '/api/database/create/' . $dbcreatedata->databaseid,
            CURLOPT_PORT => $backendPort,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true
        ));
    } else if (!empty($dbcreatedata->forcecreate)) {
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $backendAddress . '/api/database/forcecreate/' . $dbcreatedata->databaseid,
            CURLOPT_PORT => $backendPort,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true
        ));
    } else if (!empty($dbcreatedata->drop) || !empty($dbcreatedata->dropdelete)) {
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $backendAddress . '/api/database/drop/' . $dbcreatedata->databaseid,
            CURLOPT_PORT => $backendPort,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true
        ));
    }

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

    if ($respcode === 201) {
        echo $output->notification('Success', 'notifysuccess');
    } else if ($respcode === 200) {
        if (!empty($dbcreatedata->drop) || !empty($dbcreatedata->dropdelete)) {
            echo $output->notification('Dropped', 'notifysuccess');

            if (!empty($dbcreatedata->dropdelete)) {
                $DB->delete_records('database_sqlj', array('id' => $dbcreatedata->databaseid));
                echo $output->notification('Deleted', 'notifysuccess');
            }
        } else {
            echo $output->notification('Database already exists', 'notifyinfo');
        }
    } else {
        echo $resp;
        echo $output->notification('Error, try again later', 'notifyerror');
    }
} else if (has_capability('local/sqljudge:viewsensitive', context_system::instance())) {
    $dbcreate_form->display();
}

if ($dbadd_form->is_cancelled()) {
    echo $output->notification('Form submission canceled.', 'notifyproblem');
} else if ($data = $dbadd_form->get_data()) {
    // Form submitted and data is valid
    $name = $data->name;
    $description = $data->description;
    $dbms = $data->dbms;
    $dbcreationscript = $data->dbcreationscript;

    // Insert data into the database_sqlj table
    $record = new stdClass();
    $record->name = $name;
    $record->description = $description;
    $record->dbms = $dbms;
    $record->dbcreationscript = $dbcreationscript;
    $record->createdon = time();
    $record->createdby = $USER->id;

    $DB->insert_record('database_sqlj', $record);

    echo $output->notification('Data inserted successfully.', 'notifysuccess');
} else if (has_capability('local/sqljudge:viewsensitive', context_system::instance())) {
    $dbadd_form->display();
}

echo $output->footer();
?>