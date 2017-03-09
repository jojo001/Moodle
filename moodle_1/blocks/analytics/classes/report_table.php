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
 * Block XP report table.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

/**
 * Block XP report table class.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_analytics_report_table extends table_sql {

    /** @var string The key of the user ID column. */
    public $useridfield = 'id';

    /** @var block_analytics_manager XP Manager. */
    protected $analyticsmanager = null;

    /** @var block_analytics_manager XP Manager. */
    protected $analyticsoutput = null;

    /**
     * Constructor.
     *
     * @param string $uniqueid Unique ID.
     * @param int $courseid Course ID.
     * @param int $groupid Group ID.
     */
    public function __construct($uniqueid, $courseid, $groupid) {
        global $DB, $PAGE;
        parent::__construct($uniqueid);

        // Block XP stuff.
        $this->analyticsmanager = block_analytics_manager::get($courseid);
        $this->analyticsoutput = $PAGE->get_renderer('block_analytics');
        $context = context_course::instance($courseid);

        // Define columns.
        $this->define_columns(array(
            'userpic',
            'fullname',
            'lvl',
            'analytics',
            'progress',
            'actions'
        ));
        $this->define_headers(array(
            '',
            get_string('fullname'),
            get_string('level', 'block_analytics'),
            get_string('analytics', 'block_analytics'),
            get_string('progress', 'block_analytics'),
            ''
        ));

        // Get all the users that are enrolled and can earn XP.
        $ids = array();
        $users = get_enrolled_users($context, 'block/analytics:earnanalytics', $groupid);
        foreach ($users as $user) {
            $ids[$user->id] = $user->id;
        }
        unset($users);

        // Get the users which might not be enrolled or are revoked the permission, but still should
        // be displayed in the report for the teachers' benefit. We need to filter out the users which
        // are not a member of the group though.
        if (empty($groupid)) {
            $sql ='SELECT userid FROM {block_analytics} WHERE courseid = :courseid';
            $params = array('courseid' => $courseid);
        } else {
            $sql ='SELECT b.userid
                     FROM {block_analytics} b
                     JOIN {groups_members} gm
                       ON b.userid = gm.userid
                      AND gm.groupid = :groupid
                    WHERE courseid = :courseid';
            $params = array('courseid' => $courseid, 'groupid' => $groupid);
        }
        $entries = $DB->get_recordset_sql($sql, $params);
        foreach ($entries as $entry) {
            $ids[$entry->userid] = $entry->userid;
        }
        $entries->close();
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'param', true, null);

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = user_picture::fields('u') . ', COALESCE(x.lvl, 1) AS lvl, x.analytics, ' .
            context_helper::get_preload_record_columns_sql('ctx');
        $this->sql->from = "{user} u
                       JOIN {context} ctx
                         ON ctx.instanceid = u.id
                        AND ctx.contextlevel = :contextlevel
                  LEFT JOIN {block_analytics} x
                         ON (x.userid = u.id AND x.courseid = :courseid)";
        $this->sql->where = "u.id $insql";
        $this->sql->params = array_merge($inparams, array(
            'courseid' => $courseid,
            'contextlevel' => CONTEXT_USER
        ));

        // Define various table settings.
        $this->sortable(true, 'lvl', SORT_DESC);
        $this->no_sorting('userpic');
        $this->no_sorting('progress');
        $this->collapsible(false);
    }

    /**
     * Formats the column actions.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_actions($row) {
        global $OUTPUT;
        $url = new moodle_url('/blocks/analytics/report.php', array(
            'courseid' => $this->analyticsmanager->get_courseid(),
            'action' => 'edit',
            'userid' => $row->id
        ));
        return $OUTPUT->action_icon($url, new pix_icon('t/edit', get_string('edit')));
    }

    /**
     * Formats the column level.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_lvl($row) {
        return isset($row->lvl) ? $row->lvl : 1;
    }

    /**
     * Formats the column progress.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_progress($row) {
        static $fields = null;
        if ($fields === null) {
            $fields = array_flip(block_analytics_ladder_table::$analyticsfields);
        }

        $record = (object) array_intersect_key((array) $row, $fields);
        $progress = $this->analyticsmanager->get_progress_for_user($row->id, $record);
        return $this->analyticsoutput->progress_bar($progress);
    }

    /**
     * Formats the column XP.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_analytics($row) {
        return isset($row->analytics) ? $row->analytics : 0;
    }

    /**
     * Formats the column userpic.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_userpic($row) {
        global $OUTPUT;
        context_helper::preload_from_record($row);
        return $OUTPUT->user_picture($row);
    }

    /**
     * Construct the ORDER BY clause.
     *
     * We override this to ensure that XP set to null appears at the bottom, not the top.
     *
     * @param array $cols The columns.
     * @param array $textsortcols The text columns.
     * @return string
     */
    public static function construct_order_by($cols, $textsortcols = array()) {
        $newcols = array();

        // We use a foreach to maintain the order in which the fields were defined.
        foreach ($cols as $field => $sortorder) {
            if ($field == 'analytics') {
                $field = 'COALESCE(analytics, 0)';
            }
            $newcols[$field] = $sortorder;
        }

        return parent::construct_order_by($newcols, $textsortcols);
    }

    /**
     * Get SQL sort.
     *
     * Must be overridden because otherwise it calls the parent 'construct_order_by()'.
     *
     * @return string
     */
    public function get_sql_sort() {
        return static::construct_order_by($this->get_sort_columns(), array());
    }
}
