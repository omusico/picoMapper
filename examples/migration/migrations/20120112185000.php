<?php

class Version20120112185000 extends \picoMapper\Migration {


    public function up() {

        $this->addTable('tasks', 
            array(
                'id' => 'primaryKey',
                'name' => 'text'
            ),
            array(
                'user_id' => $this->addForeignKey('users', 'id')
            )
        );
       
        $this->addIndex('tasks', 'user_id');  
    }


    public function down() {

    }
}

