<?php

class Version20120112184900 extends \picoMapper\Migration {


    public function up() {

        $this->addTable('users', array(
            'id' => 'primaryKey',
            'name' => 'string'
        ));

        $this->addColumn('users', 'is_active', 'boolean');
    }


    public function down() {

    }
}

