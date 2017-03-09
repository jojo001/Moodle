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
 * Capabilities.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    // Whether or not the user can add the block.
    'block/analytics:addinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    // Whether or not a user can earn eanalyticserience point.
    'block/analytics:earnanalytics' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW
        ),
    ),

    // Whether or not the user can add the block on their dashboard.
    // We don't allow anyone to add it, the teacher/manager should add it
    // to their default dashboards.
    'block/analytics:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array()
    ),

    // Whether or not a user can see the block.
    'block/analytics:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    )
);
