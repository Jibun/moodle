<?php
 
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/workflow_diagram/datalib.php');
 
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

echo $OUTPUT->header();

if (ajaxenabled()) {
    $PAGE->requires->js_init_call('M.block_workflow_diagram.printgraph');
}

echo $OUTPUT->footer();