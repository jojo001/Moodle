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
 * Library of interface functions and constants for module newmath
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the newmath specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_newmath
 * @copyright  2017 See Lok Kan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('MOD_NEWMATH_FRANKY','mod_newmath');
define('MOD_NEWMATH_LANG','mod_newmath');
define('MOD_NEWMATH_TABLE','newmath');
define('MOD_NEWMATH_USERTABLE','newmath_attempt');
define('MOD_NEWMATH_MODNAME','newmath');
define('MOD_NEWMATH_URL','/mod/newmath');
define('MOD_NEWMATH_CLASS','mod_newmath');

define('MOD_NEWMATH_GRADEHIGHEST', 0);
define('MOD_NEWMATH_GRADELOWEST', 1);
define('MOD_NEWMATH_GRADELATEST', 2);
define('MOD_NEWMATH_GRADEAVERAGE', 3);
define('MOD_NEWMATH_GRADENONE', 4);


////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function newmath_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
		case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        default:                        return null;
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the newmath.
 *
 * @param $mform form passed by reference
 */
function newmath_reset_course_form_definition(&$mform) {
    $mform->addElement('header', MOD_NEWMATH_MODNAME . 'header', get_string('modulenameplural', MOD_NEWMATH_LANG));
    $mform->addElement('advcheckbox', 'reset_' . MOD_NEWMATH_MODNAME , get_string('deletealluserdata',MOD_NEWMATH_LANG));
}

/**
 * Course reset form defaults.
 * @param object $course
 * @return array
 */
function newmath_reset_course_form_defaults($course) {
    return array('reset_' . MOD_NEWMATH_MODNAME =>1);
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function newmath_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT l.*, cm.idnumber as cmidnumber, l.course as courseid
              FROM {" . MOD_NEWMATH_TABLE . "} l, {course_modules} cm, {modules} m
             WHERE m.name='" . MOD_NEWMATH_MODNAME . "' AND m.id=cm.module AND cm.instance=l.id AND l.course=:course";
    $params = array ("course" => $courseid);
    if ($moduleinstances = $DB->get_records_sql($sql,$params)) {
        foreach ($moduleinstances as $moduleinstance) {
            newmath_grade_item_update($moduleinstance, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * newmath attempts for course $data->courseid.
 *
 * @global stdClass
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function newmath_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', MOD_NEWMATH_LANG);
    $status = array();

    if (!empty($data->{'reset_' . MOD_NEWMATH_MODNAME})) {
        $sql = "SELECT l.id
                         FROM {".MOD_NEWMATH_TABLE."} l
                        WHERE l.course=:course";

        $params = array ("course" => $data->courseid);
        $DB->delete_records_select(MOD_NEWMATH_USERTABLE, MOD_NEWMATH_MODNAME . "id IN ($sql)", $params);

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            newmath_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletealluserdata', MOD_NEWMATH_LANG), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates(MOD_NEWMATH_MODNAME, array('available', 'deadline'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}




/**
 * Create grade item for activity instance
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $moduleinstance object with extra cmidnumber
 * @param array|object $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function newmath_grade_item_update($moduleinstance, $grades=null) {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (array_key_exists('cmidnumber', $moduleinstance)) { //it may not be always present
        $params = array('itemname'=>$moduleinstance->name, 'idnumber'=>$moduleinstance->cmidnumber);
    } else {
        $params = array('itemname'=>$moduleinstance->name);
    }

    if ($moduleinstance->grade > 0) {
        $params['gradetype']  = GRADE_TYPE_VALUE;
        $params['grademax']   = $moduleinstance->grade;
        $params['grademin']   = 0;
    } else if ($moduleinstance->grade < 0) {
        $params['gradetype']  = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$moduleinstance->grade;

        // Make sure current grade fetched correctly from $grades
        $currentgrade = null;
        if (!empty($grades)) {
            if (is_array($grades)) {
                $currentgrade = reset($grades);
            } else {
                $currentgrade = $grades;
            }
        }

        // When converting a score to a scale, use scale's grade maximum to calculate it.
        if (!empty($currentgrade) && $currentgrade->rawgrade !== null) {
            $grade = grade_get_grades($moduleinstance->course, 'mod', MOD_NEWMATH_MODNAME, $moduleinstance->id, $currentgrade->userid);
            $params['grademax']   = reset($grade->items)->grademax;
        }
    } else {
        $params['gradetype']  = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    } else if (!empty($grades)) {
        // Need to calculate raw grade (Note: $grades has many forms)
        if (is_object($grades)) {
            $grades = array($grades->userid => $grades);
        } else if (array_key_exists('userid', $grades)) {
            $grades = array($grades['userid'] => $grades);
        }
        foreach ($grades as $key => $grade) {
            if (!is_array($grade)) {
                $grades[$key] = $grade = (array) $grade;
            }
            //check raw grade isnt null otherwise we insert a grade of 0
            if ($grade['rawgrade'] !== null) {
                $grades[$key]['rawgrade'] = ($grade['rawgrade'] * $params['grademax'] / 100);
            } else {
                //setting rawgrade to null just in case user is deleting a grade
                $grades[$key]['rawgrade'] = null;
            }
        }
    }


    return grade_update('mod/' . MOD_NEWMATH_MODNAME, $moduleinstance->course, 'mod', MOD_NEWMATH_MODNAME, $moduleinstance->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $moduleinstance
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function newmath_update_grades($moduleinstance, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($moduleinstance->grade == 0) {
        newmath_grade_item_update($moduleinstance);

    } else if ($grades = newmath_get_user_grades($moduleinstance, $userid)) {
        newmath_grade_item_update($moduleinstance, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        newmath_grade_item_update($moduleinstance, $grade);

    } else {
        newmath_grade_item_update($moduleinstance);
    }
	
	//echo "updategrades" . $userid;
}

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $moduleinstance
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function newmath_get_user_grades($moduleinstance, $userid=0) {
    global $CFG, $DB;

    $params = array("moduleid" => $moduleinstance->id);

    if (!empty($userid)) {
        $params["userid"] = $userid;
        $user = "AND u.id = :userid";
    }
    else {
        $user="";

    }

	$idfield = 'a.' . MOD_NEWMATH_MODNAME . 'id';
    if ($moduleinstance->maxattempts==1 || $moduleinstance->gradeoptions == MOD_NEWMATH_GRADELATEST) {

        $sql = "SELECT u.id, u.id AS userid, a.sessionscore AS rawgrade
                  FROM {user} u,  {". MOD_NEWMATH_USERTABLE ."} a
                 WHERE u.id = a.userid AND $idfield = :moduleid
                       AND a.status = 1
                       $user";
	
	}else{
		switch($moduleinstance->gradeoptions){
			case MOD_NEWMATH_GRADEHIGHEST:
				$sql = "SELECT u.id, u.id AS userid, MAX( a.sessionscore  ) AS rawgrade
                      FROM {user} u, {". MOD_NEWMATH_USERTABLE ."} a
                     WHERE u.id = a.userid AND $idfield = :moduleid
                           $user
                  GROUP BY u.id";
				  break;
			case MOD_NEWMATH_GRADELOWEST:
				$sql = "SELECT u.id, u.id AS userid, MIN(  a.sessionscore  ) AS rawgrade
                      FROM {user} u, {". MOD_NEWMATH_USERTABLE ."} a
                     WHERE u.id = a.userid AND $idfield = :moduleid
                           $user
                  GROUP BY u.id";
				  break;
			case MOD_NEWMATH_GRADEAVERAGE:
            $sql = "SELECT u.id, u.id AS userid, AVG( a.sessionscore  ) AS rawgrade
                      FROM {user} u, {". MOD_NEWMATH_USERTABLE ."} a
                     WHERE u.id = a.userid AND $idfield = :moduleid
                           $user
                  GROUP BY u.id";
				  break;

        }

    } 

    return $DB->get_records_sql($sql, $params);
}


function newmath_get_completion_state($course,$cm,$userid,$type) {
	return newmath_is_complete($course,$cm,$userid,$type);
}


//this is called internally only 
function newmath_is_complete($course,$cm,$userid,$type) {
	 global $CFG,$DB;


	// Get module object
    if(!($moduleinstance=$DB->get_record(MOD_NEWMATH_TABLE,array('id'=>$cm->instance)))) {
        throw new Exception("Can't find module with cmid: {$cm->instance}");
    }
	$idfield = 'a.' . MOD_NEWMATH_MODNAME . 'id';
	$params = array('moduleid'=>$moduleinstance->id, 'userid'=>$userid);
	$sql = "SELECT  MAX( sessionscore  ) AS grade
                      FROM {". MOD_NEWMATH_USERTABLE ."}
                     WHERE userid = :userid AND " . MOD_NEWMATH_MODNAME . "id = :moduleid";
	$result = $DB->get_field_sql($sql, $params);
	if($result===false){return false;}
	 
	//check completion reqs against satisfied conditions
	switch ($type){
		case COMPLETION_AND:
			$success = $result >= $moduleinstance->mingrade;
			break;
		case COMPLETION_OR:
			$success = $result >= $moduleinstance->mingrade;
	}
	//return our success flag
	return $success;
}


/**
 * A task called from scheduled or adhoc
 *
 * @param progress_trace trace object
 *
 */
function newmath_dotask(progress_trace $trace) {
    $trace->output('executing dotask');
}

/**
 * Saves a new instance of the newmath into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $newmath An object from the form in mod_form.php
 * @param mod_newmath_mod_form $mform
 * @return int The id of the newly inserted newmath record
 */
function newmath_add_instance(stdClass $newmath, mod_newmath_mod_form $mform = null) {
    global $DB;

    $newmath->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record(MOD_NEWMATH_TABLE, $newmath);
}

/**
 * Updates an instance of the newmath in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $newmath An object from the form in mod_form.php
 * @param mod_newmath_mod_form $mform
 * @return boolean Success/Fail
 */
function newmath_update_instance(stdClass $newmath, mod_newmath_mod_form $mform = null) {
    global $DB;

    $newmath->timemodified = time();
    $newmath->id = $newmath->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record(MOD_NEWMATH_TABLE, $newmath);
}

/**
 * Removes an instance of the newmath from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function newmath_delete_instance($id) {
    global $DB;

    if (! $newmath = $DB->get_record(MOD_NEWMATH_TABLE, array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records(MOD_NEWMATH_TABLE, array('id' => $newmath->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function newmath_user_outline($course, $user, $mod, $newmath) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $newmath the module instance record
 * @return void, is supposed to echp directly
 */
function newmath_user_complete($course, $user, $mod, $newmath) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmath activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function newmath_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link newmath_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function newmath_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see newmath_get_recent_mod_activity()}

 * @return void
 */
function newmath_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function newmath_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function newmath_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of newmath?
 *
 * This function returns if a scale is being used by one newmath
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $newmathid ID of an instance of this module
 * @return bool true if the scale is used by the given newmath instance
 */
function newmath_scale_used($newmathid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(MOD_NEWMATH_TABLE, array('id' => $newmathid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of newmath.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any newmath instance
 */
function newmath_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists(MOD_NEWMATH_TABLE, array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}



////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function newmath_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for newmath file areas
 *
 * @package mod_newmath
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function newmath_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the newmath file areas
 *
 * @package mod_newmath
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the newmath's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function newmath_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding newmath nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the newmath module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function newmath_extend_navigation(navigation_node $newmathnode, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the newmath settings
 *
 * This function is called when the context for the page is a newmath module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $newmathnode {@link navigation_node}
 */
function newmath_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $newmathnode=null) {
}
