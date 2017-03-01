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
 * Redirect the user to the appropriate submission related page
 *
 * @package   mod_newmath
 * @category  grade
 * @copyright 2017 See Lok Kan
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once($CFG->dirroot.'/mod/newmath/lib.php');
require_once($CFG->dirroot.'/mod/newmath/locallib.php');
$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('newmath', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
//$newmath = $DB->get_record('newmath', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, false, $cm);
$PAGE->set_url('/mod/newmath/grade.php', array('id'=>$cm->id));
if (has_capability('mod/newmath:manageattempts', context_module::instance($cm->id))) {
    redirect('reports.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}