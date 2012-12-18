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
 * Displays information about all the assignment modules in the requested course
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
//require_once($CFG->dirroot . '/mod/assign/locallib.php');

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);
$PAGE->set_url('/mod/assign/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');

//add_to_log($course->id, "assign", "view all", "index.php?id=$course->id", "");
// You need mod/quiz:manage in addition to question capabilities to access this page.
//require_capability('mod/quiz:manage', $contexts->lowest());
// Print the header
$strworkflow = 'Workflow';
$PAGE->navbar->add($strworkflow);
$PAGE->set_title($strworkflow);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

require_once($CFG->dirroot .'/blocks/workflow_diagram/datalib.php');
$wdmanager = new block_workflow_diagram_manager();

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {
    $cmid = optional_param('cmid', false, PARAM_INT);
    $inidate = optional_param('initialdate', false, PARAM_RAW);
    $enddate = optional_param('finaldate', false, PARAM_RAW);
    $hours = optional_param('hours', 0, PARAM_INT);
    if ($cmid === "" || $inidate === "" || $enddate === "" || $hours === "") {
        echo "error!! inutil!";
    }else{
        $splited = explode("-", $inidate);
        $inidate = make_timestamp($splited[0], $splited[1], $splited[2]);
        
        $splited = explode("-", $enddate);
        $enddate = make_timestamp($splited[0], $splited[1], $splited[2]);
        
        //echo $cmid . ' ' . $inidate . ' ' . $enddate . ' ' . $hours;
        $wdmanager->add_modify_workflow_diagram_add_or_modify_instance($cmid, $inidate, $enddate, $hours);
    }
}else if(optional_param('delete', false, PARAM_BOOL) && confirm_sesskey()){
    $cmid = optional_param('cmid', false, PARAM_INT);
    $wdmanager->block_workflow_diagram_remove_instance($cmid);
}

$formstart = '<form method="post" action="index.php?id=' . $id . '" class="workflowform">';
$formend = '</form>';
$formhiddensave =  '<input type="hidden" name="sesskey" value="'.sesskey().'" />
                        <input type="hidden" name="savechanges" value="1" />';
$formhiddendelete =  '<input type="hidden" name="sesskey" value="'.sesskey().'" />
                        <input type="hidden" name="delete" value="1" />';

$strassignments = get_string("modulenameplural", "assign");
// Get all the appropriate data
if ($assignments = get_all_instances_in_course("assign", $course)) {
    //notice(get_string('thereareno', 'moodle', $strplural), new moodle_url('/course/view.php', array('id' => $course->id)));
    //die;
// Check if we need the closing date header
    $table = new html_table();
    $table->head = array($strassignments, get_string('duedate', 'assign'));
    $table->align = array('left', 'left', 'center');
    $table->data = array();
    foreach ($assignments as $assignment) {
        $cm = get_coursemodule_from_instance('assign', $assignment->id, 0, false, MUST_EXIST);

        $link = html_writer::link(new moodle_url('/mod/assign/view.php', array('id' => $cm->id)), $assignment->name);
        $date = '-';
        if (!empty($assignment->duedate)) {
            $date = userdate($assignment->duedate);
        }

        $row = array($link, $date);
        $table->data[] = $row;
    }
    echo html_writer::table($table);
} else {
    $noassignments = true;
}

$strquizzes = get_string("modulenameplural", "quiz");
if ($quizzes = get_all_instances_in_course("quiz", $course)) {
    //notice(get_string('thereareno', 'moodle', $strquizzes), "../../course/view.php?id=$course->id");
    //die;

    $table2 = new html_table();
    $table2->head = array($strquizzes, get_string('quizcloses', 'quiz'), get_string('startdate', 'block_workflow_diagram'), 
                    get_string('enddate', 'block_workflow_diagram'), get_string('hoursofwork', 'block_workflow_diagram'), 
                    get_string('save', 'block_workflow_diagram')." ".get_string('workflow', 'block_workflow_diagram'), 
                    get_string('delete', 'block_workflow_diagram')." ".get_string('workflow', 'block_workflow_diagram'));
    $table2->align = array('left');
    $table2->data = array();
    foreach ($quizzes as $quiz) {
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, 0, false, MUST_EXIST);
        $formhiddencm = '<input type="hidden" name="cmid" value="'.$cm->id.'" />';

        $link = html_writer::link(new moodle_url('/mod/quiz/view.php', array('id' => $cm->id)), $quiz->name);
        $date = '-';
        if ($quiz->timeclose) {
            $date = userdate($quiz->timeclose);
            $datearray = usergetdate($quiz->timeclose);
            if(strlen($datearray['mon']) === 1){
                $datearray['mon'] = '0'.$datearray['mon'];
            }
            if(strlen($datearray['mday']) === 1){
                $datearray['mday'] = '0'.$datearray['mday'];
            }
        }

        if (!$wf = $wdmanager->block_workflow_diagram_get_instance($cm->id)) {

            $inidate = $formstart.'<input type="date" name="initialdate"/>';
            if ($quiz->timeclose) {
                $enddate = '<input type="date" name="finaldate" value="'.$datearray['year']."-".$datearray['mon']."-".$datearray['mday'].'"/>';
            } else {
                $enddate = '<input type="date" name="finaldate"/>';
            }
            $hours = '<input type="text" name="hours" value="0" />';

            $boton = $formhiddensave.$formhiddencm.'<input type="submit" class="pointssubmitbutton" value="'.get_string('create', 'block_workflow_diagram').'" />'.$formend;
            $delete = '';
        } else {
            $datearray = usergetdate($wf->startdate);
            if(strlen($datearray['mon']) === 1){
                $datearray['mon'] = '0'.$datearray['mon'];
            }
            if(strlen($datearray['mday']) === 1){
                $datearray['mday'] = '0'.$datearray['mday'];
            }
            $inidate = $formstart.'<input type="date" name="initialdate" value="'.$datearray['year']."-".$datearray['mon']."-".$datearray['mday'].'"/>';
            $datearray = usergetdate($wf->finishdate);
            if(strlen($datearray['mon']) === 1){
                $datearray['mon'] = '0'.$datearray['mon'];
            }
            if(strlen($datearray['mday']) === 1){
                $datearray['mday'] = '0'.$datearray['mday'];
            }
            $enddate = '<input type="date" name="finaldate" value="'.$datearray['year']."-".$datearray['mon']."-".$datearray['mday'].'"/>';
            $hours = '<input type="text" name="hours" value="'.$wf->hours.'" />';
            $boton = $formhiddensave.$formhiddencm.'<input type="submit" class="pointssubmitbutton" value="'.get_string('update', 'block_workflow_diagram').'" />'.$formend;
            $delete = $formstart.$formhiddendelete.$formhiddencm.'<input type="submit" class="pointssubmitbutton" value="'.get_string('delete', 'block_workflow_diagram').'" />'.$formend;
        }

        $row = array($link, $date, $inidate, $enddate, $hours, $boton, $delete);
        $table2->data[] = $row;
    }

    echo html_writer::table($table2);
} else {
    $noquizes = true;
}

if ($noassignments && $noquizes) {
    notice(get_string('thereareno', 'moodle', $stractivities), "../../course/view.php?id=$course->id");
    die;
}

echo $OUTPUT->footer();

