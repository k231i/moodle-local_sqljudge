<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2022070404;
$plugin->requires = 2020061500;     //Moodle 3.9
$plugin->component = 'assignfeedback_sqljudge';
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = 'v0.0.1';
$plugin->dependencies = array('local_sqljudge' => 2022070400);
