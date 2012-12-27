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
 * Library of functions for database manipulation.
 *
 * Other main libraries:
 * - weblib.php - functions that produce web output
 * - moodlelib.php - general-purpose Moodle functions
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Number of seconds to wait before updating lastaccess information in DB.
 */
define('LASTACCESS_UPDATE_SECS', 60);

class block_workflow_diagram_manager {

    public function get_workflow_from_coursemodule($cmid, $courseid = 0, $strictness = IGNORE_MISSING) {
        global $DB;

        $params = array('cmid' => $cmid);

        $courseselect = "";

        if ($courseid) {
            $courseselect = "AND cm.course = :courseid";
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT wd.*
              FROM {block_workflow_diagram} wd
                   JOIN {course_modules} cm ON cm.id = wd.cmid
             WHERE wd.cmid = :cmid
                   $courseselect";

        return $DB->get_record_sql($sql, $params, $strictness);
    }
    
    /**
     * Get the total hours of all the workflow of a course by a given date
     * @param integer $courseid
     * @param integer $date
     * @return float with sum of hours with name hours
     */
    public function get_hoursperday_by_course_date($courseid, $date, $strictness = IGNORE_MISSING) {
        global $DB;
        
        $midnightdate = usergetmidnight($date); //Midnight of the current day
        
        $params = array('date1' => $midnightdate, 'date2' => $midnightdate+(24*3600), 'course' => $courseid );
        $aux1 = usergetmidnight($date);
        
        $sql = 'SELECT SUM(wf.hoursperday) as hours
        FROM {block_workflow_diagram} wf 
        JOIN {course_modules} cm ON cm.id = wf.cmid
        WHERE :date1 >= wf.startdate AND :date2 <= wf.finishdate AND cm.course = :course';
    
        return $DB->get_record_sql($sql, $params, $strictness)->hours;
     }
     
    /**
     * Add a workflow diagram instance
     * @param integer $cmid
     * @param integer $startdate
     * @param integer $finishdate
     * @param integer $hours
     * @param float $hoursperday
     * @return id of workflow or false if already added
     */
    public function block_workflow_diagram_add_instance($cmid, $startdate, $finishdate, $hours, $hoursperday) {
        global $DB;

        $wd = $this->block_workflow_diagram_get_instance($cmid);

        if (empty($wd)) {
            $wd->cmid = $cmid;
            $wd->startdate = $startdate;
            $wd->finishdate = $finishdate;
            $wd->hours = $hours;
            $wd->hoursperday = $hoursperday;
            return $DB->insert_record('block_workflow_diagram', $wd);
        } else {
            return false;
        }
    }
    
    /**
     * Add or modifies a workflow diagram instance
     * @param integer $cmid
     * @param integer $startdate
     * @param integer $finishdate
     * @param integer $hours
     * @param float $hoursperday
     * @return id of workflow or false if already added
     */
     public function add_modify_workflow_diagram_add_or_modify_instance($cmid, $startdate, $finishdate, $hours, $hoursperday) {
        global $DB;

        $existingField = $this->block_workflow_diagram_get_instance($cmid);

        $wd->cmid = $cmid;
        $wd->startdate = $startdate;
        $wd->finishdate = $finishdate;
        $wd->hours = $hours;
        $wd->hoursperday = $hoursperday;
        if (empty($existingField)) {
            return $DB->insert_record('block_workflow_diagram', $wd);
        } else {
            $wd->id = $existingField->id;
            return $DB->update_record('block_workflow_diagram', $wd);
        }
    }
    
    /**
     * Return a workflow diagram of a coursemodule
     * @param integer $cmid
     * @return array of workflow
     */
    public function block_workflow_diagram_get_instance($cmid) {
        global $DB;
        return $DB->get_record('block_workflow_diagram',
                array('cmid' => $cmid));
    }
    
    /**
     * Delete a workflow diagram
     * @param integer $cmid
     * @return bool true
     */
    public function block_workflow_diagram_remove_instance($cmid) {
        global $DB;
        return $DB->delete_records('block_workflow_diagram',
                array('cmid' => $cmid));
    }
    
    /**
     * Get activity name
     * @param int $cmid
     * @return activity name
     * 
     */
    public function block_workflow_diagram_get_activity_name($cmid) {
        global $DB;
        
        $params = array('cmid'=>$cmid);

        if (!$modulename = $DB->get_field_sql("SELECT md.name
                                                 FROM {modules} md
                                                 JOIN {course_modules} cm ON cm.module = md.id
                                                WHERE cm.id = :cmid", $params)) {
            return false;
        }

        $sql = "SELECT m.name
                  FROM {course_modules} cm
                       JOIN {".$modulename."} m ON m.id = cm.instance
                 WHERE cm.id = :cmid";

        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Get array of activities
     * @param int $courseid
     * @param date $date
     * @return array of activities (id and hoursperday)
     * 
     */
    public function block_workflow_diagram_get_activities_array_for_day($courseid, $date) {
        global $DB;
        
        //$midnightdate = usergetmidnight($date);
        $params = array('date1' => $date, 'date2' => $date+DAYSECS, 'course' => $courseid );
        
        $sql = 'SELECT cm.id, wf.hoursperday
        FROM {block_workflow_diagram} wf 
        JOIN {course_modules} cm ON cm.id = wf.cmid
        WHERE :date1 >= wf.startdate AND :date2 <= wf.finishdate AND cm.course = :course';
        
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Get a JSON string with all the chart information
     * @param integer $courseid
     * @return string json representation of data array
     */
    public function block_workflow_diagram_get_json_array_for_chart($courseid, $time) {
        
        //Get current week
        $unixtime = usergetmidnight($time); //Seconds passed since...
        for ($i=0; $i<7; $i++) {
            $auxtime = $unixtime + ($i * DAYSECS);
            $date[$i] = usergetdate($auxtime);

            $result = $this->block_workflow_diagram_get_activities_array_for_day($courseid, $auxtime);            
            $grapharray[$i] = array('date' => $date[$i]['mday'].'/'.$date[$i]['mon'].'/'.$date[$i]['year']);
            
             if($result){ 
                reset($result);    
                for($j=0; $j<count($result); $j++){
                    
                    $name = $this->block_workflow_diagram_get_activity_name(current($result)->id);
                    
                     /* See http://php.net/manual/en/function.array-push.php
                     * 
                     * What the next line does is push back the hoursperday on the array
                     * whith de id between the brackets.
                     * 
                     * Ex: $data[$key] = $value;
                     * The "+0" is to convert the data to a number.
                     */
                    $grapharray[$i][current($result)->id.' - '.$name->name] = current($result)->hoursperday+0;
                    
                    next($result);
                }
            }
        }
        return json_encode($grapharray);
    }

}

?>
