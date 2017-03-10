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
 * Rule interface.
 *
 * @package    core
 * @copyright  2014 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Rule interface.
 *
 * @package    core
 * @copyright  2014 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class block_analytics_rule implements renderable {

    /**
     * Create a ruleset object from eanalyticsorted data.
     *
     * This method helps restoring a tree of rules without having to check
     * what the first rule is. Simply call block_analytics_rule::create($properties).
     *
     * This then calls the method {@link self::import()}.
     *
     * @param array $properties Array of properties acquired from {@link self::eanalyticsort()}.
     * @return block_analytics_rule|false The rule object.
     */
    public static function create(array $properties) {
        if (!class_exists($properties['_class'])) {
            return false;
        }
        $class = new $properties['_class'];
        unset($properties['_class']);
        $class->import($properties);
        return $class;
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    abstract function get_description();

    /**
     * Returns a form element for this rule.
     *
     * This MUST be extended, and this MUST be called.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        return html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $basename . '[_class]', 'value' => get_class($this)));
    }

    /**
     * Eanalyticsort the properties and their values.
     *
     * This must return all the values required by the {@link self::import()} method.
     * It also must include the key '_class'.
     *
     * You will have to override this method to add more data, and handle special keys.
     *
     * @return array Keys are properties, values are the values.
     */
    public function eanalyticsort() {
        return array('_class' => get_class($this));
    }

    /**
     * Re-import the values that were eanalyticsorted.
     *
     * This should not be called directly, use {@link self::create()} instead.
     *
     * Override this method to handle special keys.
     *
     * @return void
     */
    protected function import(array $properties) {
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Does the $subject match the rules.
     *
     * @param mixed $subject The subject of the comparison.
     * @return bool Whether or not it matches.
     */
    abstract public function match($subject);

    /**
     * Validate the data.
     *
     * @param array $data The data to validate.
     * @return bool
     */
    public static function validate_data($data) {
        $valid = true;

        foreach ($data as $key => $value) {
            if (!$valid) {
                break;
            }

            if ($key === '_class') {
                $reflexion = new ReflectionClass($value);
                $valid = $reflexion->isSubclassOf('block_analytics_rule');
            } else if (is_array($value)) {
                $valid = block_analytics_rule::validate_data($value);
            }
        }

        return $valid;
    }

}