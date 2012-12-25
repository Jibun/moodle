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
 * Workflow Diagram Block page.
 *
 * @package   blocks
 * @subpackage workflow_diagram
 * @author  Ivan Latorre Negrell
 * @copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_workflow_diagram extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_workflow_diagram');
    }

    function get_content() {
        global $CFG, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        if (!isloggedin()) {
            return $this->content;
        }
        
        $course = $this->page->course;
        
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('moodle/course:manageactivities', $context)) {
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/workflow_diagram/index.php?id='.$course->id.'">'.get_string('editwf', 'block_workflow_diagram').'</a>';
        }
        
        //Link a veure gràfic passant arguments
        $url = new moodle_url('/blocks/workflow_diagram/graphview.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        $this->content->items[] = html_writer::link($url, get_string('viewgraphic', 'block_workflow_diagram'));
        
        //No funciona no sé per què (es bloqueja la pàgina)
        //$wf = new block_workflow_diagram_manager();
        //$dayworkload = wf->get_hoursperday_by_course_date($course->id, time());
        $this->content->items[] = 'id '.$course->id;
        
        return $this->content;
    }
}
