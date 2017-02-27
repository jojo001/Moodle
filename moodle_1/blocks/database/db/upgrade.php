<?php
 
function xmldb_block_database_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    /// Add a new column newcol to the mdl_myqtype_options
    if ($oldversion < 2017022301) {
           if ($oldversion < 2017022301) {

        // Define field flag to be added to block_database.
        $table = new xmldb_table('block_database');
        $field = new xmldb_field('flag', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'created');

        // Conditionally launch add field flag.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Database savepoint reached.
        upgrade_block_savepoint(true, 2017022301, 'database');
    }

    }

    return true;
}
?>
