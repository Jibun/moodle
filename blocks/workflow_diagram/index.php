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
 * Edition page for the workflow block
 *
 * @package   blocks
 * @subpackage workflow_diagram
 * @author Ivan Latorre Negrell
 * @copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);
$PAGE->set_url('/mod/assign/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');

// You need moodle/course:manageactivities in addition to question capabilities to access this page.
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:manageactivities', $context);

// Print the header
$strworkflow = get_string("pluginname", "block_workflow_diagram");
$PAGE->navbar->add($strworkflow);
$PAGE->set_title($strworkflow);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

require_once($CFG->dirroot . '/blocks/workflow_diagram/datalib.php');
$wdmanager = new block_workflow_diagram_manager();

// Look if the buttons create, update or delete have been pressed.
if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {
    $cmid = optional_param('cmid', false, PARAM_INT);
    $inidateday = optional_param('initialdateday', false, PARAM_RAW);
    $inidatemonth = optional_param('initialdatemonth', false, PARAM_RAW);
    $inidateyear = optional_param('initialdateyear', false, PARAM_RAW);
    $enddateday = optional_param('finaldateday', false, PARAM_RAW);
    $enddatemonth = optional_param('finaldatemonth', false, PARAM_RAW);
    $enddateyear = optional_param('finaldateyear', false, PARAM_RAW);
    $hours = optional_param('hours', 0, PARAM_INT);
    
    $inidate = make_timestamp($inidateyear, $inidatemonth, $inidateday);
    $enddate = make_timestamp($enddateyear, $enddatemonth, $enddateday);
    
    if ($hours === "" || ($enddate-$inidate)<0) {
        echo get_string('editerror', 'block_workflow_diagram');
    } else {
        $period = abs($enddate - $inidate);
        if ($period = floor($period / DAYSECS)) {
            $hoursperday = round($hours / $period, 2);
        } else {
            $hoursperday = 0;
        }
        $wdmanager->add_modify_workflow_diagram_add_or_modify_instance($cmid, $inidate, $enddate, $hours, $hoursperday);
    }
} else if (optional_param('delete', false, PARAM_BOOL) && confirm_sesskey()) {
    $cmid = optional_param('cmid', false, PARAM_INT);
    $wdmanager->block_workflow_diagram_remove_instance($cmid);
}

$formstart = '<form method="post" action="index.php?id=' . $id . '" class="workflowform">';
$formend = '</form>';
$formhiddensave = '<input type="hidden" name="sesskey" value="' . sesskey() . '" />
                        <input type="hidden" name="savechanges" value="1" />';
$formhiddendelete = '<input type="hidden" name="sesskey" value="' . sesskey() . '" />
                        <input type="hidden" name="delete" value="1" />';

//Array with all the activities
$activitiesarray = array('assign', 'chat', 'choice', 'data', 'forum', 'glossary', 'lesson', 
    'lti', 'quiz', 'scorm', 'survey', 'wiki', 'workshop');

$noactivities = true;

//Print a table for each activity
for ($i = 0; $i < 13; $i++) {
    $stractivities = get_string("modulenameplural", $activitiesarray[$i]);
    $activities = get_all_instances_in_course($activitiesarray[$i], $course);
    if ($activities) {
        $nodatefields = false;
        $strtime1 = 'nothing';
        $strtime2 = 'nothing';
        $time1 = 'nothing';
        $time2 = 'nothing';
        if($activitiesarray[$i] === 'quiz'){
            $strtime1 = 'quizopens';
            $strtime2 = 'quizcloses';
            $time1 = 'timeopen';
            $time2 = 'timeclose';
        }else if($activitiesarray[$i] === 'assign'){
            $strtime1 = 'allowsubmissionsfromdate';
            $strtime2 = 'duedate';
            $time1 = 'allowsubmissionsfromdate';
            $time2 = 'duedate';
        }else if($activitiesarray[$i] === 'forum'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'data'){
            $strtime1 = 'availablefromdate';
            $strtime2 = 'availabletodate';
            $time1 = 'timeavailablefrom';
            $time2 = 'timeavailableto';
        }else if($activitiesarray[$i] === 'lti'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'chat'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'choice'){
            $strtime1 = 'choiceopen';
            $strtime2 = 'choiceclose';
            $time1 = 'timeopen';
            $time2 = 'timeclose';
        }else if($activitiesarray[$i] === 'survey'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'glossary'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'lesson'){
            $strtime1 = 'lessonopens';
            $strtime2 = 'lessoncloses';
            $time1 = 'available';
            $time2 = 'deadline';
        }else if($activitiesarray[$i] === 'workshop'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'wiki'){
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'scorm'){
            $strtime1 = 'scormopen';
            $strtime2 = 'duedate';
            $time1 = 'timeopen';
            $time2 = 'timeclose';
        }
        
        $table = new html_table();
        if($nodatefields){
            $table->head = array($stractivities, get_string('startdate', 'block_workflow_diagram'),
            get_string('enddate', 'block_workflow_diagram'), get_string('hoursofwork', 'block_workflow_diagram'),
            get_string('save', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'),
            get_string('delete', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'));
            $table->align = array('left', 'left', 'left', 'left', 'center', 'center');
        }else{
            $table->head = array($stractivities, get_string($strtime1, $activitiesarray[$i]), 
            get_string($strtime2, $activitiesarray[$i]), get_string('startdate', 'block_workflow_diagram'),
            get_string('enddate', 'block_workflow_diagram'), get_string('hoursofwork', 'block_workflow_diagram'),
            get_string('save', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'),
            get_string('delete', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'));
            $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'center', 'center');
        }
        $table->data = array();
        foreach ($activities as $activity) {
            $cm = get_coursemodule_from_instance($activitiesarray[$i], $activity->id, 0, false, MUST_EXIST);
            $formhiddencm = '<input type="hidden" name="cmid" value="' . $cm->id . '" />';

            $link = $formstart . html_writer::link(new moodle_url('/mod/'.$activitiesarray[$i].'/view.php', array('id' => $cm->id)), $activity->name);
            if(!$nodatefields){
                $date1 = '-';
                if ($activity->$time1) {
                    $date1 = userdate($activity->$time1);
                }
                $date2 = '-';
                if ($activity->$time2) {
                    $date2 = userdate($activity->$time2);
                }
            }

            if (!$wf = $wdmanager->block_workflow_diagram_get_instance($cm->id)) {
                if(!$nodatefields){
                    if ($activity->$time1) {
                        $inidate = html_writer::select_time('days', 'initialdateday', $activity->$time1).
                                html_writer::select_time('months', 'initialdatemonth', $activity->$time1).
                                html_writer::select_time('years', 'initialdateyear', $activity->$time1);
                    } else {
                        $inidate = html_writer::select_time('days', 'initialdateday').
                                html_writer::select_time('months', 'initialdatemonth').
                                html_writer::select_time('years', 'initialdateyear');
                    }
                    if ($activity->$time2) {
                        $enddate = html_writer::select_time('days', 'finaldateday', $activity->$time2).
                                html_writer::select_time('months', 'finaldatemonth', $activity->$time2).
                                html_writer::select_time('years', 'finaldateyear', $activity->$time2);
                    } else {
                        $enddate = html_writer::select_time('days', 'finaldateday').
                                html_writer::select_time('months', 'finaldatemonth').
                                html_writer::select_time('years', 'finaldateyear');
                    }
                }
                
                $hours = '<input type="text" size ="2" name="hours" value="0" />';

                $boton = $formhiddensave . $formhiddencm . '<input type="submit" class="pointssubmitbutton" value="' . get_string('create', 'block_workflow_diagram') . '" />' . $formend;
                $delete = '';
            } else {
                $inidate = html_writer::select_time('days', 'initialdateday', $wf->startdate).
                                html_writer::select_time('months', 'initialdatemonth', $wf->startdate).
                                html_writer::select_time('years', 'initialdateyear', $wf->startdate);
                $enddate = html_writer::select_time('days', 'finaldateday', $wf->finishdate).
                                html_writer::select_time('months', 'finaldatemonth', $wf->finishdate).
                                html_writer::select_time('years', 'finaldateyear', $wf->finishdate);
                $hours = '<input type="text" size ="2" name="hours" value="' . $wf->hours . '" />';
                $boton = $formhiddensave . $formhiddencm . '<input type="submit" class="pointssubmitbutton" value="' . get_string('update', 'block_workflow_diagram') . '" />' . $formend;
                $delete = $formstart . $formhiddendelete . $formhiddencm . '<input type="submit" class="pointssubmitbutton" value="' . get_string('delete', 'block_workflow_diagram') . '" />' . $formend;
            }

            if ($nodatefields)
                $row = array($link, $inidate, $enddate, $hours, $boton, $delete);
            else
                $row = array($link, $date1, $date2, $inidate, $enddate, $hours, $boton, $delete);
            
            $table->data[] = $row;
        }

        echo html_writer::table($table);
        
        $noactivities = false;
    }
}

//If there isn't any activity
if ($noactivities) {
    notice(get_string('thereareno', 'moodle', $stractivities), "../../course/view.php?id=$course->id");
    die;
}

echo $OUTPUT->footer();

