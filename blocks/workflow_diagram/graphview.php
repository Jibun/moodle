<?php

// This file is part of Moodle - http://moodle.org/
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

/**
 * Shows information and the diagram
 *
 * @package   blocks
 * @subpackage workflow_diagram
 * @author  Sergi Rodríguez
 * @copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$PAGE->set_title(get_string("pluginname", "block_workflow_diagram"));
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading($course->fullname);

$settingsnode = $PAGE->settingsnav->add(get_string('wfsettings', 'block_workflow_diagram'));
$wfurl = new moodle_url('/blocks/workflow_diagram/graphview.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('graphview', 'block_workflow_diagram'), $wfurl);
$editnode->make_active();

//Printar header
echo $OUTPUT->header();

$i = 0;
if (optional_param('nextweek', false, PARAM_BOOL) || optional_param('lastweek', false, PARAM_BOOL) && confirm_sesskey()) {
    $i = optional_param('counter', false, PARAM_INTEGER);
    if (optional_param('nextweek', false, PARAM_BOOL))
        $i++;
    elseif (optional_param('lastweek', false, PARAM_BOOL))
        $i--;
}
    $formstart = '<form method="post" action="graphview.php?id=' . $id . '" class="workflowform">
        <input type="hidden" name="sesskey" value="' . sesskey() . '" />
            <input type="hidden" name="courseid" value="' . $courseid . '" />
                <input type="hidden" name="blockid" value="' . $blockid . '" />
                    <input type="hidden" name="counter" value="' . $i . '" />';
    $formend = '</form>';
    $formhiddennext = '<input type="hidden" name="nextweek" value="1" />';
    $formhiddenlast = '<input type="hidden" name="lastweek" value="1" />';
    $button1 = $formstart . $formhiddennext. '<input type="submit" class="pointssubmitbutton" value="' . get_string('next', 'block_workflow_diagram') . '" />' . $formend;
    $button2 = $formstart . $formhiddenlast. '<input type="submit" class="pointssubmitbutton" value="' . get_string('last', 'block_workflow_diagram') . '" />' . $formend;

//Print page
if (ajaxenabled()) {
    
    $result = $wdmanager->get_hoursperday_by_course_date($courseid, time());
    echo html_writer::tag('div', get_string('todayworkload', 'block_workflow_diagram').$result.'<br /><br />', array('id' => 'daylyhours'));
    
    $time = time() + ($i * WEEKSECS);
    $jsondata = $wdmanager->block_workflow_diagram_get_json_array_for_chart($courseid, $time);
    echo html_writer::tag('div', get_string('weekdiagram', 'block_workflow_diagram'), array('id' => 'mychart')); //Equival a echo '<div id=mychart></div>';
    $PAGE->requires->js_init_call('M.block_workflow_diagram.printgraph', array($jsondata));
}
else {
    echo html_writer::tag('div', get_string('jsdisabled', 'block_workflow_diagram'), array('id' => 'mychart')); //Div que conté la gràfica
}

    echo html_writer::tag('div', '<br />'.$button2.'<br \>'.$button1, array('id' => 'buttons'));
    //echo html_writer::tag('div', $button2, array('id' => 'buttons'));


//Print moodle elements
echo $OUTPUT->footer();