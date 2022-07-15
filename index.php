<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

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

echo $output->footer();