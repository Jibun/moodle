<?php
 
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/workflow_diagram/datalib.php');
$wdmanager = new block_workflow_diagram_manager();

global $DB, $OUTPUT, $PAGE;
 
// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
 
// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_workflow_diagram', $courseid);
}
 
require_login($course);
$PAGE->set_url('/blocks/workflow_diagram/graphview.php', array('id' => $courseid));
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading($course->fullname);

$settingsnode = $PAGE->settingsnav->add(get_string('wfsettings', 'block_workflow_diagram'));
$wfurl = new moodle_url('/blocks/workflow_diagram/graphview.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('graphview', 'block_workflow_diagram'), $wfurl);
$editnode->make_active();

//Printar header
echo $OUTPUT->header();


/*
 * Printar pàgina principal
 */


if (ajaxenabled()) { //Si tenim javascript
    
    $result = $wdmanager->get_hoursperday_by_course_date($courseid, time());
    echo html_writer::tag('div', get_string('todayworkload', 'block_workflow_diagram').$result, array('id' => 'daylyhours'));
    
    $jsonatr1 = $wdmanager->block_workflow_diagram_get_json_array_for_chart($courseid);
    echo html_writer::tag('div', null, array('id' => 'mychart')); //Equival a echo '<div id=mychart></div>';
    $PAGE->requires->js_init_call('M.block_workflow_diagram.printgraph', array($jsonatr1));
}
else {
    echo html_writer::tag('div', get_string('jsdisabled', 'block_workflow_diagram'), array('id' => 'mychart')); //Div que conté la gràfica
}

/*
 * Fi de pàgina principal
 */

//Printar resta d'elements de moodle
echo $OUTPUT->footer();