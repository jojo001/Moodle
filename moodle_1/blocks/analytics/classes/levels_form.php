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
 * Block XP levels form.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Block XP levels form class.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_analytics_levels_form extends moodleform {

    /** @var block_analytics_manager The XP manager. */
    protected $manager;

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $this->manager = $this->_customdata['manager'];

        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'hdrgen', get_string('general', 'form'));

        $mform->addElement('text', 'levels', get_string('levelcount', 'block_analytics'));
        $mform->addRule('levels', get_string('required'), 'required');
        $mform->setType('levels', PARAM_INT);

        if ($this->manager->get_config('enablecustomlevelbadges')) {
            $mform->addElement('static', '', '', get_string('changelevelformhelp', 'block_analytics'));
        }

        $mform->addElement('selectyesno', 'usealgo', get_string('usealgo', 'block_analytics'));
        $mform->setDefault('usealgo', 1);

        $mform->addElement('text', 'baseanalytics', get_string('baseanalytics', 'block_analytics'));
        $mform->setDefault('baseanalytics', block_analytics_manager::DEFAULT_BASE);
        $mform->disabledIf('baseanalytics', 'usealgo', 'eq', 0);
        $mform->setType('baseanalytics', PARAM_INT);
        $mform->setAdvanced('baseanalytics', true);

        $mform->addElement('text', 'coefanalytics', get_string('coefanalytics', 'block_analytics'));
        $mform->setDefault('coefanalytics', block_analytics_manager::DEFAULT_COEF);
        $mform->disabledIf('coefanalytics', 'usealgo', 'eq', 0);
        $mform->setType('coefanalytics', PARAM_FLOAT);
        $mform->setAdvanced('coefanalytics', true);

        $mform->addElement('submit', 'updateandpreview', get_string('updateandpreview', 'block_analytics'));
        $mform->registerNoSubmitButton('updateandpreview');

        // First level.
        $mform->addElement('header', 'hdrlevel1', get_string('levelx', 'block_analytics', 1));
        $mform->addElement('static', 'lvlanalytics_1', get_string('analyticsrequired', 'block_analytics'), 0);

        $mform->addelement('hidden', 'insertlevelshere');
        $mform->setType('insertlevelshere', PARAM_BOOL);

        $mform->addElement('static', 'warn', '', $OUTPUT->notification(get_string('levelswillbereset', 'block_analytics'), 'notifyproblem'));

        $this->add_action_buttons();

    }

    /**
     * Definition after data.
     *
     * @return void
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Ensure that the values are not wrong, the validation on save will catch those problems.
        $levels = max((int) $mform->eanalyticsortValue('levels'), 2);
        $base = max((int) $mform->eanalyticsortValue('baseanalytics'), 1);
        $coef = max((float) $mform->eanalyticsortValue('coefanalytics'), 1.001);

        $defaultlevels = block_analytics_manager::get_levels_with_algo($levels, $base, $coef);

        // Add the levels.
        for ($i = 2; $i <= $levels; $i++) {
            $el =& $mform->createElement('header', 'hdrlevel' . $i, get_string('levelx', 'block_analytics', $i));
            $mform->insertElementBefore($el, 'insertlevelshere');

            $el =& $mform->createElement('text', 'lvlanalytics_' . $i, get_string('analyticsrequired', 'block_analytics'));
            $mform->insertElementBefore($el, 'insertlevelshere');
            $mform->setType('lvlanalytics_' . $i, PARAM_INT);
            $mform->disabledIf('lvlanalytics_' . $i, 'usealgo', 'eq', 1);
            if ($mform->eanalyticsortValue('usealgo') == 1) {
                // Force the constant value when the algorightm is used.
                $mform->setConstant('lvlanalytics_' . $i, $defaultlevels[$i]);
            }

            $el =& $mform->createElement('text', 'lvldesc_' . $i, get_string('leveldesc', 'block_analytics'));
            $mform->insertElementBefore($el, 'insertlevelshere');
            $mform->addRule('lvldesc_' . $i, get_string('maximumchars', '', 255), 'maxlength', 255);
            $mform->setType('lvldesc_' . $i, PARAM_NOTAGS);
        }
    }

    /**
     * Get the submitted data.
     *
     * @return Array with levels and levelsdata.
     */
    public function get_data() {
        $mform =& $this->_form;
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        // Rearranging the information.
        $newdata = array(
            'levels' => $data->levels,
            'levelsdata' => null
        );

        $newdata['levelsdata'] = array(
            'usealgo' => $data->usealgo,
            'base' => $data->baseanalytics,
            'coef' => $data->coefanalytics,
            'analytics' => array(
                '1' => 0
            ),
            'desc' => array(
                '1' => ''
            )
        );
        for ($i = 2; $i <= $data->levels; $i++) {
            $newdata['levelsdata']['analytics'][$i] = $data->{'lvlanalytics_' . $i};
            $newdata['levelsdata']['desc'][$i] = $data->{'lvldesc_' . $i};
        }

        return $newdata;
    }

    /**
     * Set the default values.
     *
     * This translates the data from the format returned by get_data().
     *
     * @param array $data In the format returned by get_data().
     */
    public function set_data($data) {
        $levels = $data['levels'];
        $levelsdata = $data['levelsdata'];
        if ($levelsdata) {
            $data['usealgo'] = $levelsdata['usealgo'];
            $data['baseanalytics'] = $levelsdata['base'];
            $data['coefanalytics'] = $levelsdata['coef'];
            for ($i = 2; $i <= $levels; $i++) {
                $data['lvlanalytics_' . $i] = $levelsdata['analytics'][$i];
                $data['lvldesc_' . $i] = isset($levelsdata['desc'][$i]) ? $levelsdata['desc'][$i] : '';
            }
        }
        unset($data['levelsdata']);
        parent::set_data($data);
    }

    /**
     * Data validate.
     *
     * @param array $data The data submitted.
     * @param array $files The files submitted.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = array();
        if ($data['levels'] < 2) {
            $errors['levels'] = get_string('errorlevelsincorrect', 'block_analytics');
        }

        // Validating the XP points.
        if (!isset($errors['levels'])) {
            $lastanalytics = 0;
            for ($i = 2; $i <= $data['levels']; $i++) {
                $key = 'lvlanalytics_' . $i;
                $analytics = isset($data[$key]) ? (int) $data[$key] : -1;
                if ($analytics <= 0) {
                    $errors['lvlanalytics_' . $i] = get_string('invalidanalytics', 'block_analytics');
                } else if ($lastanalytics >= $analytics) {
                    $errors['lvlanalytics_' . $i] = get_string('erroranalyticsrequiredlowerthanpreviouslevel', 'block_analytics');
                }
                $lastanalytics = $analytics;
            }
        }

        return $errors;
    }

}
