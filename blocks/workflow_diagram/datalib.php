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
        
        $params = array('date1' => $date, 'date2' => $date, 'course' => $courseid );
        
        $sql = 'SELECT SUM(wf.hoursperday) as hours
        FROM {block_workflow_diagram} wf 
        JOIN {course_modules} cm ON cm.id = wf.cmid 
        WHERE :date1 >= wf.startdate AND :date2 <= wf.finishdate AND cm.course = :course';
        
        return $DB->get_record_sql($sql, $params, $strictness);
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

}

?>
