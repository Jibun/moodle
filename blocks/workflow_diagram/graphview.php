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

//Printar header
echo $OUTPUT->header();


/*
 * Printar pàgina principal
 */


if (ajaxenabled()) { //Si tenim javascript
    
    $atr1 = array ( //Prova de passar array PHP a JSON
        array('date' => '5/1/2010', 'calcul' => 2, 'fisica' => 0),
        array('date' => '5/2/2010', 'calcul' => 3, 'fisica' => 1),
        );
    $jsonatr1 = json_encode($atr1);
    echo html_writer::tag('div', $jsonatr1, array('id' => 'debugtext'));

    //El següent equival a echo '<div id=mychart></div>';
    echo html_writer::tag('div', null, array('id' => 'mychart')); //Div que conté la gràfica
    $PAGE->requires->js_init_call('M.block_workflow_diagram.printgraph', array());
}
else {
    echo html_writer::tag('div', get_string('jsdisabled', 'block_workflow_diagram'), array('id' => 'mychart')); //Div que conté la gràfica
}

/*
 * Fi de pàgina principal
 */

//Printar resta d'elements de moodle
echo $OUTPUT->footer();