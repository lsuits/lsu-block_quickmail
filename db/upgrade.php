<?php

function xmldb_block_quickmail_upgrade($oldversion) {
    global $DB;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012021014) {
        $table = new xmldb_table('block_quickmail_alternate');

        $field = new xmldb_field('id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, true, null, null);

        $table->addField($field);

        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, null, 'id');

        $table->addField($field);

        $field = new xmldb_field('address');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null,
            XMLDB_NOTNULL, false, null, 'courseid');

        $table->addField($field);

        $field = new xmldb_field('valid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, '0', 'address');

        $table->addField($field);

        $key = new xmldb_key('PRIMARY');
        $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));

        $table->addKey($key);

        $dbman->create_table($table);
    }

    return $result;
}
