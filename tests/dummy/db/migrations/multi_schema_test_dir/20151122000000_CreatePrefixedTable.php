<?php

class CreatePrefixedTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('
            CREATE TABLE ' . $this->get_prefix() . 'some_table (id INT)
        ');
    }

    public function down()
    {
        $this->execute('
            DROP TABLE ' . $this->get_prefix() . 'some_table
        ');
    }

    private function get_prefix()
    {
        $dsn = $this->get_adapter()->get_dsn();
        return $dsn['prefix'];
    }
}
