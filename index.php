<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');


class sqljudge_dbadd_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'header', 
            'general', 
            'Add Database');

        $mform->addElement(
            'text', 
            'name', 
            'Name:');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement(
            'text', 
            'description', 
            'Description:');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement(
            'select', 
            'dbms', 
            'DBMS:', 
            sqljudge_get_supported_dbms_list());
        $mform->setType('dbms', PARAM_ALPHA);

        $mform->addElement(
            'textarea', 
            'dbcreationscript', 
            'DB Creation Script:');
        $mform->setType('dbcreationscript', PARAM_TEXT);

        $this->add_action_buttons(true, 'Submit');
    }
}

//$context = get_system_context();
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
$form = new sqljudge_dbadd_form();

if ($form->is_cancelled()) {
    // Handle form cancellation
    echo $output->notification('Form submission canceled.', 'notifyproblem');
} else if ($data = $form->get_data()) {
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
} else {
    // Display the form
    echo $output->heading('Insert Data', 2);
    $form->display();
}

echo $output->footer();
?>
