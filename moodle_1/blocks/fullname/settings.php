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
 * fullname block caps.
 *
 * @package    block_fullname
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('sampleheader',
                                         get_string('headerconfig', 'block_fullname'),
                                         get_string('descconfig', 'block_fullname')));

$settings->add(new admin_setting_configcheckbox('fullname/foo',
                                                get_string('labelfoo', 'block_fullname'),
                                                get_string('descfoo', 'block_fullname'),
                                                '0'));

$settings->add(new admin_setting_configtext('block_fullname/url', get_string('url', 'block_fullname'),
            get_string('url_details', 'block_fullname'), 'https://quizlet.com/132695231/scatter/embed', PARAM_RAW)); 
                                                
$settings->add(new admin_setting_configtext('block_fullname/width', get_string('width', 'block_fullname'),
            get_string('width_details', 'block_fullname'), 600, PARAM_INT)); 
            
$settings->add(new admin_setting_configtext('block_fullname/height', get_string('height', 'block_fullname'),
            get_string('height_details', 'block_fullname'), 400, PARAM_INT));   
            
$options = array();
$options['course']=get_string('course');
$options['popup']=get_string('popup');
            
$settings->add(new admin_setting_configselect('block_fullname/pagelayout', get_string('pagelayout', 'block_fullname'),
            get_string('pagelayout_details', 'block_fullname'),'course', $options));                                                       
                                                
