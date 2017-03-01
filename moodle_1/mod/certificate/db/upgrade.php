<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * This file keeps track of upgrades to the certificate module
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_certificate_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager();

  
    if ($oldversion < 2017022300) {
        // Fix previous upgrades.

        $table = new xmldb_table('certificate');

        $field = new xmldb_field('borderstyle', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, 0, '0');
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('printwmark', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, 0, '0');
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('printhours', XMLDB_TYPE_CHAR, '255', null, false, 0, null);
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('printsignature', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, 0, '0');
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('printseal', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, 0, '0');
        $dbman->change_field_default($table, $field);

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2017022300, 'certificate');
    }

    return true;
}
