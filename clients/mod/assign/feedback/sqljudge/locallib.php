<?php
// This file is part of Moodle - https://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php'); //for get_grade_options()
$locallibfile = $CFG->dirroot . '/local/sqljudge/judgelib.php';
file_exists($locallibfile) AND require_once $locallibfile;
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once(dirname(__FILE__) . '/lib.php');
//require_once('testcase_form.php');

class assign_feedback_sqljudge extends assign_feedback_plugin {
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_sqljudge');
    }


    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE, $DB;
        // if updating an assignment
        // $update = optional_param('update', 0, PARAM_INT);
        // if (!empty($update)) {
        //     $sqljudge = $DB->get_record('assignment_sqlj', 
        //         array('assignment' => $this->assignment->get_instance()->id));
        // }
        // script of database creation
        $mform->addElement('filemanager', 'createdbscript', 
            get_string('createdbscript', 'assignfeedback_sqljudge'), null,
            array(
                'subdirs' => 0,
                'maxbytes' => 1024 * 1024 * 256,
                'maxfiles' => 1,
                'accepted_types' => array('.sql')
        ));
        $mform->hideIf('createdbscript', 'assignfeedback_sqljudge_enabled', 'notchecked');
        // script for checking answers
        $mform->addElement('filemanager', 'checkscript', 
            get_string('checkscript', 'assignfeedback_sqljudge'), null,
            array(
                'subdirs' => 0,
                'maxbytes' => 1024 * 1024 * 4,
                'maxfiles' => 1,
                'accepted_types' => array('.sql')
        ));
        $mform->hideIf('checkscript', 'assignfeedback_sqljudge_enabled', 'notchecked');
        // correct answer script
        $mform->addElement('filemanager', 'correctanswer', 
            get_string('correctanswer', 'assignfeedback_sqljudge'), null,
            array(
                'subdirs' => 0,
                'maxbytes' => 1024 * 1024 * 4,
                'maxfiles' => 1,
                'accepted_types' => array('.sql')
        ));
        $mform->hideIf('correctanswer', 'assignfeedback_sqljudge_enabled', 'notchecked');
        // database management system
        $choices = sqljudge_get_supported_dbms_list();
        $mform->addElement('select', 'dbms', 
            get_string('dbms', 'assignfeedback_sqljudge'), $choices);
        $mform->setDefault('dbms',
            !empty($sqljudge) ? $sqljudge->dbms : get_config('local_sqljudge', 'defaultdbms'));
        $mform->hideIf('dbms', 'assignfeedback_sqljudge_enabled', 'notchecked');
        // max time
        $choices = get_max_times();
        $mform->addElement('select', 'maxtime',
            get_string('maxtime', 'assignfeedback_sqljudge'), $choices);
        $mform->setDefault('maxtime',
            !empty($sqljudge) ? $sqljudge->maxtime : get_config('local_sqljudge', 'maxtimelimit'));
        $mform->hideIf('maxtime', 'assignfeedback_sqljudge_enabled', 'notchecked');
        // max ram usage
        $choices = get_max_ram_usages();
        $mform->addElement('select', 'maxramusage',
            get_string('maxramusage', 'assignfeedback_sqljudge'), $choices);
        $mform->setDefault('maxramusage',
            !empty($sqljudge) ? $sqljudge->maxramusage : get_config('local_sqljudge', 'maxramlimit'));
        $mform->hideIf('maxramusage', 'assignfeedback_sqljudge_enabled', 'notchecked');
    }


    public function save_settings(stdClass $data) {
        return false; // TODO: add db table and remove this
        
        global $DB;

        if (!empty($errors = $this->form_validation($data))) {
            $table = new html_table();
            foreach ($errors as $error => $value) 
                $table->data[] = array($error, $value);
            $output = html_writer::table($table);
            $this->set_error($output);
            return false;
        }
        $exists = $DB->get_record('assignment_sqlj', 
            array('assignment' => $this->assignment->get_instance()->id)) ? true : false;
        if (!$exists) 
            return add_instance($data, $this->assignment->get_instance()->id);
        else 
            return update_instance($data, $this->assignment->get_instance()->id);
    }
}