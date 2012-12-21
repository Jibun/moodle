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
$strworkflow = get_string("pluginname", "block_workflow_diagram");
$PAGE->navbar->add($strworkflow);
$PAGE->set_title($strworkflow);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

require_once($CFG->dirroot . '/blocks/workflow_diagram/datalib.php');
$wdmanager = new block_workflow_diagram_manager();

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {
    $cmid = optional_param('cmid', false, PARAM_INT);
    $inidate = optional_param('initialdate', false, PARAM_RAW);
    $enddate = optional_param('finaldate', false, PARAM_RAW);
    $hours = optional_param('hours', 0, PARAM_INT);
    if ($cmid === "" || $inidate === "" || $enddate === "" || $hours === "") {
        echo "error!! inutil!";
    } else {
        $splited = explode("-", $inidate);
        $inidate = make_timestamp($splited[0], $splited[1], $splited[2]);

        $splited = explode("-", $enddate);
        $enddate = make_timestamp($splited[0], $splited[1], $splited[2]);

        $period = abs($enddate - $inidate);
        if ($period = floor($period / DAYSECS)) {
            $hoursperday = round($hours / $period, 2);
        } else {
            $hoursperday = 0;
        }
        //echo $cmid . ' ' . $inidate . ' ' . $enddate . ' ' . $hours;
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

$activitiesarray = array('assign', 'chat', 'choice', 'data', 'forum', 'glossary', 'lesson', 
    'lti', 'quiz', 'scorm', 'survey', 'wiki', 'workshop');

$noactivities = true;

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
            //nada
            /*$strtime1 = 'assesstimestart';
            $strtime2 = 'assesstimefinish';
            $time1 = 'assesstimestart';
            $time2 = 'assesstimefinish';*/
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'data'){
            $strtime1 = 'availablefromdate';
            $strtime2 = 'availabletodate';
            $time1 = 'timeavailablefrom';
            $time2 = 'timeavailableto';
        }else if($activitiesarray[$i] === 'lti'){
            //nada
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'chat'){
            //nada
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'choice'){
            $strtime1 = 'choiceopen';
            $strtime2 = 'choiceclose';
            $time1 = 'timeopen';
            $time2 = 'timeclose';
        }else if($activitiesarray[$i] === 'survey'){
            //nada
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'glossary'){
            //nada
            /*$strtime1 = 'assesstimestart';
            $strtime2 = 'assesstimefinish';
            $time1 = 'assesstimestart';
            $time2 = 'assesstimefinish';*/
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'lesson'){
            $strtime1 = 'lessonopens';
            $strtime2 = 'lessoncloses';
            $time1 = 'available';
            $time2 = 'deadline';
        }else if($activitiesarray[$i] === 'workshop'){
            //nada
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'wiki'){
            //nada
            $nodatefields = true;
        }else if($activitiesarray[$i] === 'scorm'){
            $strtime1 = 'scormopen';
            $strtime2 = 'duedate';
            $time1 = 'timeopen';
            $time2 = 'timeclose';
        }
        
        
        $table = new html_table();
        if($nodatefields)
            $table->head = array($stractivities, get_string('startdate', 'block_workflow_diagram'),
            get_string('enddate', 'block_workflow_diagram'), get_string('hoursofwork', 'block_workflow_diagram'),
            get_string('save', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'),
            get_string('delete', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'));
        else
            $table->head = array($stractivities, get_string($strtime1, $activitiesarray[$i]), 
            get_string($strtime2, $activitiesarray[$i]), get_string('startdate', 'block_workflow_diagram'),
            get_string('enddate', 'block_workflow_diagram'), get_string('hoursofwork', 'block_workflow_diagram'),
            get_string('save', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'),
            get_string('delete', 'block_workflow_diagram') . " " . get_string('workflow', 'block_workflow_diagram'));
        $table->align = array('left');
        $table->data = array();
        foreach ($activities as $activity) {
            $cm = get_coursemodule_from_instance($activitiesarray[$i], $activity->id, 0, false, MUST_EXIST);
            $formhiddencm = '<input type="hidden" name="cmid" value="' . $cm->id . '" />';

            $link = html_writer::link(new moodle_url('/mod/'.$activitiesarray[$i].'/view.php', array('id' => $cm->id)), $activity->name);
            if(!$nodatefields){
                $date1 = '-';
                if ($activity->$time1) {
                    $date1 = userdate($activity->$time1);
                    $datearray1 = usergetdate($activity->$time1);
                }
                $date2 = '-';
                if ($activity->$time2) {
                    $date2 = userdate($activity->$time2);
                    $datearray2 = usergetdate($activity->$time2);
                }
            }

            if (!$wf = $wdmanager->block_workflow_diagram_get_instance($cm->id)) {
                if(!$nodatefields){
                    if (strlen($datearray1['mon']) === 1) {
                        $datearray1['mon'] = '0' . $datearray1['mon'];
                    }
                    if (strlen($datearray1['mday']) === 1) {
                        $datearray1['mday'] = '0' . $datearray1['mday'];
                    }
                    if (strlen($datearray2['mon']) === 1) {
                        $datearray2['mon'] = '0' . $datearray2['mon'];
                    }
                    if (strlen($datearray2['mday']) === 1) {
                        $datearray2['mday'] = '0' . $datearray2['mday'];
                    }
                    
                    if ($activity->$time1) {
                        $inidate = '<input type="date" name="initialdate" value="' . $datearray1['year'] . "-" . $datearray1['mon'] . "-" . $datearray1['mday'] . '"/>';
                    } else {
                        $inidate = $formstart . '<input type="date" name="initialdate"/>';
                    }
                    if ($activity->$time2) {
                        $enddate = '<input type="date" name="finaldate" value="' . $datearray2['year'] . "-" . $datearray2['mon'] . "-" . $datearray2['mday'] . '"/>';
                    } else {
                        $enddate = '<input type="date" name="finaldate"/>';
                    }
                }
                
                $hours = '<input type="text" name="hours" value="0" />';

                $boton = $formhiddensave . $formhiddencm . '<input type="submit" class="pointssubmitbutton" value="' . get_string('create', 'block_workflow_diagram') . '" />' . $formend;
                $delete = '';
            } else {
                $datearray = usergetdate($wf->startdate);
                if (strlen($datearray['mon']) === 1) {
                    $datearray['mon'] = '0' . $datearray['mon'];
                }
                if (strlen($datearray['mday']) === 1) {
                    $datearray['mday'] = '0' . $datearray['mday'];
                }
                $inidate = $formstart . '<input type="date" name="initialdate" value="' . $datearray['year'] . "-" . $datearray['mon'] . "-" . $datearray['mday'] . '"/>';
                $datearray = usergetdate($wf->finishdate);
                if (strlen($datearray['mon']) === 1) {
                    $datearray['mon'] = '0' . $datearray['mon'];
                }
                if (strlen($datearray['mday']) === 1) {
                    $datearray['mday'] = '0' . $datearray['mday'];
                }
                $enddate = '<input type="date" name="finaldate" value="' . $datearray['year'] . "-" . $datearray['mon'] . "-" . $datearray['mday'] . '"/>';
                $hours = '<input type="text" name="hours" value="' . $wf->hours . '" />';
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

if ($noactivities) {
    notice(get_string('thereareno', 'moodle', $stractivities), "../../course/view.php?id=$course->id");
    die;
}

/*if ($noassignments && $noquizes && $noforums && $noltis && $nochats && $nochoices && $nosurveys && $noglossarys && $nolessons && $noworkshops && $nowikis && $noscorms) {
    notice(get_string('thereareno', 'moodle', $stractivities), "../../course/view.php?id=$course->id");
    die;
}*/

echo $OUTPUT->footer();

