<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once(dirname(__FILE__) . '/lib.php');

class assign_feedback_sqljudge extends assign_feedback_plugin {
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_sqljudge');
    }

    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE, $DB;

        // get existing sqljudge settings
        $update = optional_param('update', 0, PARAM_INT);
        if (!empty($update)) {
            $sqljudge = $DB->get_record('assignment_sqlj', 
                array('assignment' => $this->assignment->get_instance()->id));
        }

        // test database
        $mform->addElement('select',  'testdb',  
            get_string('testdb', 'assignfeedback_sqljudge'),  
            get_databases());
        $mform->setDefault('testdb', empty($sqljudge) ? 1 : $sqljudge->testdb);
        $mform->hideIf('testdb', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // script for checking answers
        $mform->addElement('textarea', 'checkscript', 
            get_string('checkscript', 'assignfeedback_sqljudge'), 
            'wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('checkscript', empty($sqljudge) ? '' : $sqljudge->checkscript);
        $mform->hideIf('checkscript', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // correct answer script
        $mform->addElement('textarea', 'correctanswer', 
            get_string('correctanswer', 'assignfeedback_sqljudge'), 
            'wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('correctanswer', empty($sqljudge) ? '' : $sqljudge->correctanswer);
        $mform->hideIf('correctanswer', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // banned or required keywords/phrases
        $mform->addElement('textarea', 'mustcontain', 
            get_string('mustcontain', 'assignfeedback_sqljudge'), 
            'wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('mustcontain', empty($sqljudge) ? '' : $sqljudge->mustcontain);
        $mform->hideIf('mustcontain', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // hint
        $mform->addElement('textarea', 'hint', 
            get_string('hint', 'assignfeedback_sqljudge'), 
            'wrap="virtual" rows="20" cols="50"');
        $mform->setDefault('hint', empty($sqljudge) ? '' : $sqljudge->hint);
        $mform->hideIf('hint', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // max time
        $choices = get_max_times();
        $mform->addElement('select', 'timelimit',
            get_string('maxtime', 'assignfeedback_sqljudge'), $choices);
        $mform->setDefault('timelimit', empty($sqljudge) 
            ? get_config('local_sqljudge', 'maxtimelimit') 
            : $sqljudge->timelimit);
        $mform->hideIf('timelimit', 'assignfeedback_sqljudge_enabled', 'notchecked');
    }

    public function save_settings(stdClass $data) {
        global $DB;

        $exists = $DB->get_record('assignment_sqlj', 
            array('assignment' => $this->assignment->get_instance()->id)) 
                ? true : false;
        if (!$exists) 
            return add_instance($data, $this->assignment->get_instance()->id);
        else 
            return update_instance($data, $this->assignment->get_instance()->id);
    }
    
    public function view_header() {
        global $USER;
        $submission = $this->assignment->get_user_submission($USER->id, false);
        $output = $this->view_judge_info() . 
            '<div class="py-2">';
        if (!empty($submission)) {
            $url = new moodle_url('/local/sqljudge/check.php', array('id' => $submission->id));
            $output .= "<a href='$url' class='btn btn-info' type='button'>" . 
                get_string('check', 'assignfeedback_sqljudge') . "</a>";
        }
        return $output . '</div>';
    }

    function view_judge_info() {
        global $DB;

        $assignment_sqlj = $DB->get_record('assignment_sqlj', 
            array('assignment' => $this->assignment->get_instance()->id));

        $table = new html_table();
        $table->id = 'assignment_sqljudge_information';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('30%', '');

        $testdb = $DB->get_record('database_sqlj', 
            array('id' => $assignment_sqlj->testdb));

        $table->data[] = array(
            get_string('dbms', 'assignfeedback_sqljudge'),
            $testdb->dbms);

        $table->data[] = array(
            get_string('dbname', 'assignfeedback_sqljudge'),
            $testdb->name);

        $table->data[] = array(
            get_string('dbdescription', 'assignfeedback_sqljudge'),
            $testdb->description);

        $table->data[] = array(
            get_string('timelimit', 'assignfeedback_sqljudge'),
            $assignment_sqlj->timelimit . ' ' . get_string('sec'));

        return html_writer::table($table);
    }

    public function view_summary(stdClass $grade, &$showviewlink) {
        global $DB;

        $showviewlink = true;

        $submission = $this->assignment->get_user_submission($grade->userid, false);
        $sqlj_submission = $this->get_or_add_sqlj_submission($submission->id);

        $statusstyle = $sqlj_submission->status == SQLJ_STATUS_ACCEPTED 
            ? 'notifysuccess' 
            : 'notifyproblem';

        return html_writer::tag('span', 
            get_string('status' . $sqlj_submission->status, 'local_sqljudge'), 
            array('class' => $statusstyle));
    }

    public function view(stdClass $grade) {
        global $DB, $OUTPUT;

        $table = new html_table();
        $table->id = 'assignment_sqljudge_summary';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('30%', '80%');

        $submission = $this->assignment->get_user_submission($grade->userid, false);
        $sqlj_submission = $this->get_or_add_sqlj_submission($submission->id);

        // Status
        $itemname = get_string('status', 'assignfeedback_sqljudge') . ' ' . 
        $OUTPUT->help_icon('status', 'assignfeedback_sqljudge');
        $item = get_string('notavailable');
        if (isset($sqlj_submission->status)) {
            $itemstyle = $sqlj_submission->status == SQLJ_STATUS_ACCEPTED 
                ? 'label label-success' 
                : 'label label-warning';
            $item = html_writer::tag('h5', 
                html_writer::tag('span', 
                    get_string('status' . $sqlj_submission->status, 'local_sqljudge'), 
                    array('class' => $itemstyle)));
            if (has_capability('mod/assign:grade', $this->assignment->get_context())) {
                $attributes = array(
                    'href' => new moodle_url('/local/sqljudge/check.php', array('id' => $submission->id)), 
                    'class' => 'btn btn-info btn-sm');
                $item .= html_writer::tag('a',
                    get_string('check', 'assignfeedback_sqljudge'), $attributes);
            }
        }
        $table->data[] = array($itemname, $item);

        // Output
        $itemname = get_string('output', 'assignfeedback_sqljudge');
        $item = empty($sqlj_submission->output)
            ? get_string('notavailable')
            : $sqlj_submission->output;
        $table->data[] = array($itemname, $item);

        // Hint
        if ($sqlj_submission->status === SQLJ_STATUS_WRONG_ANSWER && 
            !empty($this->assignment->hint)) {
                $itemname = get_string('hint', 'assignfeedback_sqljudge');
                $item = $this->assignment->hint;
                $table->data[] = array($itemname, $item);
        }
        
        // Tested on
        $itemname = get_string('testedon', 'assignfeedback_sqljudge');
        $item = empty($sqlj_submission->testedon)
            ? get_string('notavailable')
            : userdate($sqlj_submission->testedon);
        $table->data[] = array($itemname, $item);

        $output = html_writer::table($table);
        return $output;
    }

    function get_or_add_sqlj_submission($submissionId) {
        global $DB;

        $sqlj_submission = $DB->get_record('assignment_sqlj_submission', 
            array('submission' => $submissionId));

        if (empty($sqlj_submission)) {
            $sqlj_submission = new stdClass();
            $sqlj_submission->submission = $submissionId;
            $sqlj_submission->status = SQLJ_STATUS_PENDING;
            $sqlj_submission->id = $DB->insert_record('assignment_sqlj_submission', $sqlj_submission);
        }

        return $sqlj_submission;
    }

    public function is_empty(stdClass $grade) {
        global $DB;

        $submission = $this->assignment->get_user_submission($grade->userid, false);
        $sqlj_submission = $DB->get_record('assignment_sqlj_submission', 
            array('submission' => $submission->id));
        return is_null($sqlj_submission);
    }
    public function delete_instance() {
        global $CFG, $DB;

        $submissions = $DB->get_records('assign_submission', 
            array('assignment' => $this->assignment->get_instance()->id));

        foreach ($submissions as $submission)
            if (!$DB->delete_records('assignment_sqlj_submission', 
                array('submission' => $submission->id))) 
                    return false;

        if (!$DB->delete_records('assignment_sqlj', 
            array('assignment' => $this->assignment->get_instance()->id)))
                return false;

        return true;
    }
}