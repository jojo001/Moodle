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
 * A mod_newmath adhoc task
 *
 * @package    mod_newmath
 * @copyright  2017 See Lok Kan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_newmath\event;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/newmath/lib.php');

/**
 * A mod_newmath adhoc task
 *
 * @package    mod_newmath
 * @since      Moodle 2.7
 * @copyright  2017 See Lok Kan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class newmath_adhoc extends \core\task\adhoc_task {
                                                                     
   	 /**
     *  Run the tasks
     */
	 public function execute(){
		$trace = new \text_progress_trace();
		$cd =  $this->get_custom_data();;
		//$trace->output($cd->somedata)
        	newmath_dotask($trace);
	}
		
}

