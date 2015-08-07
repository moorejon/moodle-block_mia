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
 * Newblock block caps.
 *
 * @package    block_mia
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_mia extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_mia');
    }

    function get_content() {
        global $CFG, $OUTPUT, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content->text = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            //$this->context->text .= "site context";
        }

        if (! empty($this->config->text)) {
            //$this->content->text .= $this->config->text;
        }
        //$this->content->text = 'User ID:'. $USER->id;
        $this->content->text = get_advice();
        //$this->content->text = 'Hello World';
        return $this->content;
    }



    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}

function get_progression(){
    global $CFG, $OUTPUT, $USER, $DB, $COURSE;
    $advice = "";
    $sql = "select
            m.name AS Activity_Type,
            count(cm.module)  AS module_count,
            cm.module
            from {user_enrolments} ue
            join {user} us on us.id = ue.userid
            join {enrol} e on e.id = ue.enrolid

            join {course} c on c.id = e.courseid

            JOIN {course_modules} cm ON c.id = cm.course
            JOIN {modules} m ON cm.module = m.id

            join {context} ct on (c.id = ct.instanceid and ct.contextlevel = 50)
            join {role_assignments} ra on (ct.id = ra.contextid and ue.userid = ra.userid and (ra.roleid = 3 OR ra.roleid = 4))
            WHERE
            us.id = $USER->id
            AND c.id = $COURSE->id
            AND cm.visible = 1
            GROUP BY m.name";
    $levels = array();
    $cnt = 0;
    $debug = "";
    if($activities = $DB->get_records_sql($sql)){
        $debug .="finding matches....<br/>";
        foreach( $activities as $activity ) {

            //$debug .= "$activity->activity_type $activity->module_count <br/>";
            switch ($activity->activity_type) {
                // Level 1
                case 'resource':
                case 'scorm':
                case 'book':
                    $levels[1] = 1;
                    $debug .= 'Level 1<br/>';
                    //$cnt++;
                    break;
                // Level 2
                case 'forum':
                    $levels[2] = 1;
                    $debug .= 'Level 2<br/>';
                    //$cnt++;
                    break;
                // Level 3
                case 'quiz':
                case 'assign':
                    $levels[3] = 1;
                $debug .= 'Level 3<br/>';
                    //$cnt++;
                    break;
                // Level 4
                case 'wiki':
                case 'glossary':
                case 'database':
                    $levels[4] = 1;
                $debug .= 'Level 4<br/>';
                    //$cnt++;
                    break;
                // Level 5 (needs additional check for interactivity)
                case 'forum':
                    $levels[5] = 1;
                    $debug .= 'Level 5<br/>';
                    //$cnt++;
                    break;
                // Level 6 (needs additional checking)
                case 'level 6':
                    //$levels[2] = 1;
                    break;
                // Level 7 (need to check if it's an external resource / other checks needed)
                case 'url':
                    $levels[7] = 1;
                    $debug .= 'Level 7<br/>';
                    //$cnt++;
                    break;
                // Level 8
                case 'survey':
                    $levels[8] = 1;
                    $debug .= 'Level 8<br/>';
                    //$cnt++;
                    break;
                // Level 9
                case 'workshop':
                    $levels[9] = 1;
                    $debug .= 'Level 9<br/>';
                    //$cnt++;
                    break;
            }
        }
    }

    //return $cnt;
    $progression = 0;
    for( $i = 1; $i < 10; $i++){
        if( isset($levels[$i]) ){
            $progression = $i;
        }
        else {
            break;
        }
    }
    $debug .= "===============<br/>You're Level $progression<br/>";
    $cnt = count($levels);
    $debug .= "Level Matches $cnt<br/>";
    return $progression;


}

function get_advice(){
    global $CFG, $OUTPUT, $USER, $DB, $COURSE;
    $progression = get_progression();
    $advice = '';
    // Make a suggestion
    switch ($progression) {
        // Level 1
        /*
        case 'resource':
        case 'scorm':
        case 'book':
        */
        case 0:
            $advice .= "Have you thought about adding a <a href='http://localhost:8888/moodle29/course/modedit.php?add=resource&type=&course=$COURSE->id&section=1'>Resource</a>?<br/><br/><a href='https://docs.moodle.org/29/en/Resources'>Resource documentation</a>";

            //$cnt++;
            break;
        // Level 2
        //case 'forum':
        case 1:
            $advice .= "Have you thought about adding a <a href='http://localhost:8888/moodle29/course/modedit.php?add=forum&type=&course=$COURSE->id&section=1'>Forum</a>?<br/><br/><a href='https://docs.moodle.org/29/en/Forum_module'>Forum documentation</a>";

            //$cnt++;
            break;
        // Level 3
        /*case 'quiz':
        case 'assign':*/
        case 2:
            $levels[3] = 1;
            $advice .= "Have you thought about adding a <a href='http://localhost:8888/moodle29/course/modedit.php?add=quiz&type=&course=$COURSE->id&section=1'>Quiz</a>?<br/><br/><a href='https://docs.moodle.org/29/en/Quiz_module'>Quiz documentation</a>";
            //$cnt++;
            break;
        // Level 4
        /*case 'wiki':
        case 'glossary':
        case 'database':*/
        case 3:
            $levels[4] = 1;
            $advice .= 'Great you have started to use some interactive tools! Are you facilitating discussions in Forums, asking questions, and or guiding learners? <br/>';
            //$cnt++;
            break;
        // Level 5 (needs additional check for interactivity)
        /*case 'forum':*/
        case 4:
            $levels[5] = 1;
            $advice .= 'Debugging:<br>Progression 5<br/>';
            //$cnt++;
            break;
        // Level 6 (needs additional checking)
        /*case 'level 6':*/
        case 5:
            //$levels[2] = 1;
            break;
        // Level 7 (need to check if it's an external resource / other checks needed)
        /*case 'url':*/
        case 6:
            $levels[7] = 1;
            $advice .= 'Debugging:<br>Progression 7<br/>';
            //$cnt++;
            break;
        // Level 8
        /*case 'survey':*/
        case 8:
            $levels[8] = 1;
            $advice .= 'Debugging:<br>Progression 8<br/>';
            //$cnt++;
            break;
        // Level 9
        /*case 'workshop':*/
        case 9:
            $levels[9] = 1;
            $advice .= 'Debugging:<br>Progression 9<br/>';
            //$cnt++;
            break;
    }
    //$advice .= $debug;
    return $advice;
}
