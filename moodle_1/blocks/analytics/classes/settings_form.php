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
 * Block XP settings form.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Block XP settings form class.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_analytics_settings_form extends moodleform {

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $defaultconfig = $this->_customdata['defaultconfig'];

        $mform->addElement('header', 'hdrgeneral', get_string('general'));

        $mform->addElement('selectyesno', 'enabled', get_string('enableanalyticsgain', 'block_analytics'));
        $mform->setDefault('enabled', $defaultconfig->enabled);
        $mform->addHelpButton('enabled', 'enableanalyticsgain', 'block_analytics');

        $mform->addElement('selectyesno', 'enableinfos', get_string('enableinfos', 'block_analytics'));
        $mform->setDefault('enableinfos', $defaultconfig->enableinfos);
        $mform->addHelpButton('enableinfos', 'enableinfos', 'block_analytics');

        $mform->addElement('selectyesno', 'enablelevelupnotif', get_string('enablelevelupnotif', 'block_analytics'));
        $mform->setDefault('enablelevelupnotif', $defaultconfig->enablelevelupnotif);
        $mform->addHelpButton('enablelevelupnotif', 'enablelevelupnotif', 'block_analytics');

        $mform->addElement('header', 'hdrladder', get_string('ladder', 'block_analytics'));

        $mform->addElement('selectyesno', 'enableladder', get_string('enableladder', 'block_analytics'));
        $mform->setDefault('enableladder', $defaultconfig->enableladder);
        $mform->addHelpButton('enableladder', 'enableladder', 'block_analytics');

        $mform->addElement('select', 'identitymode', get_string('anonymity', 'block_analytics'), array(
            block_analytics_manager::IDENTITY_OFF => get_string('hideparticipantsidentity', 'block_analytics'),
            block_analytics_manager::IDENTITY_ON => get_string('displayparticipantsidentity', 'block_analytics'),
        ));
        $mform->setDefault('identitymode', $defaultconfig->identitymode);
        $mform->addHelpButton('identitymode', 'anonymity', 'block_analytics');
        $mform->disabledIf('identitymode', 'enableladder', 'eq', 0);

        $mform->addElement('select', 'neighbours', get_string('limitparticipants', 'block_analytics'), array(
            0 => get_string('displayeveryone', 'block_analytics'),
            1 => get_string('displayoneneigbour', 'block_analytics'),
            2 => get_string('displaynneighbours', 'block_analytics', 'two'),
            3 => get_string('displaynneighbours', 'block_analytics', 'three'),
            4 => get_string('displaynneighbours', 'block_analytics', 'four'),
            5 => get_string('displaynneighbours', 'block_analytics', 'five'),
        ));
        $mform->setDefault('neighbours', $defaultconfig->neighbours);
        $mform->addHelpButton('neighbours', 'limitparticipants', 'block_analytics');
        $mform->disabledIf('neighbours', 'enableladder', 'eq', 0);

        $mform->addElement('select', 'rankmode', get_string('ranking', 'block_analytics'), array(
            block_analytics_manager::RANK_OFF => get_string('hiderank', 'block_analytics'),
            block_analytics_manager::RANK_ON => get_string('displayrank', 'block_analytics'),
            block_analytics_manager::RANK_REL => get_string('displayrelativerank', 'block_analytics'),
        ));
        $mform->setDefault('rankmode', $defaultconfig->rankmode);
        $mform->addHelpButton('rankmode', 'ranking', 'block_analytics');
        $mform->disabledIf('rankmode', 'enableladder', 'eq', 0);

        $mform->addElement('header', 'hdrcheating', get_string('cheatguard', 'block_analytics'));

        $mform->addElement('text', 'maxactionspertime', get_string('maxactionspertime', 'block_analytics'));
        $mform->setDefault('maxactionspertime', $defaultconfig->maxactionspertime);
        $mform->addHelpButton('maxactionspertime', 'maxactionspertime', 'block_analytics');
        $mform->setType('maxactionspertime', PARAM_INT);

        $mform->addElement('text', 'timeformaxactions', get_string('timeformaxactions', 'block_analytics'));
        $mform->setDefault('timeformaxactions', $defaultconfig->timeformaxactions);
        $mform->addHelpButton('timeformaxactions', 'timeformaxactions', 'block_analytics');
        $mform->setType('timeformaxactions', PARAM_INT);

        $mform->addElement('text', 'timebetweensameactions', get_string('timebetweensameactions', 'block_analytics'));
        $mform->setDefault('timebetweensameactions', $defaultconfig->timebetweensameactions);
        $mform->addHelpButton('timebetweensameactions', 'timebetweensameactions', 'block_analytics');
        $mform->setType('timebetweensameactions', PARAM_INT);

        $mform->addElement('header', 'hdrloggin', get_string('logging', 'block_analytics'));

        $mform->addElement('advcheckbox', 'enablelog', get_string('enablelogging', 'block_analytics'));
        $mform->setDefault('enablelog', $defaultconfig->enablelog);

        $options = array(
            '0' => get_string('forever', 'block_analytics'),
            '1' => get_string('for1day', 'block_analytics'),
            '3' => get_string('for3days', 'block_analytics'),
            '7' => get_string('for1week', 'block_analytics'),
            '30' => get_string('for1month', 'block_analytics'),
        );
        $mform->addElement('select', 'keeplogs', get_string('keeplogs', 'block_analytics'), $options);
        $mform->setDefault('keeplogs', $defaultconfig->keeplogs);

        $this->add_action_buttons();
    }

}
