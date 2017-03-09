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
 * Block XP backup steplib.
 *
 * @package    block_analytics
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block XP backup structure step class.
 *
 * @package    block_analytics
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_analytics_block_structure_step extends backup_block_structure_step {

    /**
     * Define structure.
     */
    protected function define_structure() {
        global $DB;

        $userinfo = $this->get_setting_value('users');

        // Define each element separated.
        $analyticsconfig = new backup_nested_element('config', array('courseid'), array(
            'enabled', 'enablelog', 'keeplogs', 'levels', 'lastlogpurge', 'enableladder', 'enableinfos', 'levelsdata',
            'enablelevelupnotif', 'enablecustomlevelbadges', 'maxactionspertime', 'timeformaxactions', 'timebetweensameactions',
            'identitymode', 'rankmode', 'neighbours'
        ));
        $analyticsfilters = new backup_nested_element('filters');
        $analyticsfilter = new backup_nested_element('filter', array('courseid'), array('ruledata', 'points', 'sortorder'));
        $analyticslevels = new backup_nested_element('analyticss');
        $analyticslevel = new backup_nested_element('analytics', array('courseid'), array('userid', 'analytics', 'lvl'));
        $analyticslogs = new backup_nested_element('logs');
        $analyticslog = new backup_nested_element('log', array('courseid'), array('userid', 'eventname', 'analytics', 'time'));

        // Prepare the structure.
        $analytics = $this->prepare_block_structure($analyticsconfig);

        $analyticsfilters->add_child($analyticsfilter);
        $analytics->add_child($analyticsfilters);

        if ($userinfo) {
            $analyticslevels->add_child($analyticslevel);
            $analytics->add_child($analyticslevels);

            $analyticslogs->add_child($analyticslog);
            $analytics->add_child($analyticslogs);
        }

        // Define sources.
        $analyticsconfig->set_source_table('block_analytics_config', array('courseid' => backup::VAR_COURSEID));
        $analyticsfilter->set_source_table('block_analytics_filters', array('courseid' => backup::VAR_COURSEID));
        $analyticslevel->set_source_table('block_analytics', array('courseid' => backup::VAR_COURSEID));
        $analyticslog->set_source_table('block_analytics_log', array('courseid' => backup::VAR_COURSEID));

        // Annotations.
        $analyticslevel->annotate_ids('user', 'userid');
        $analyticslog->annotate_ids('user', 'userid');
        $analytics->annotate_files('block_analytics', 'badges', null, context_course::instance($this->get_courseid())->id);

        // Return the root element.
        return $analytics;
    }
}
