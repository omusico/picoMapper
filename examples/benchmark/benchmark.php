<?php

require __DIR__.'/../../src/picoMapper.php';
require __DIR__.'/../../../picoTools/chrono.php';

use \picoTools\Chrono;


class Version01 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('table_a', 
            array(
                'id' => 'primaryKey',
                'data' => 'string'
            )
        );

        $this->addTable('table_b',
            array(
                'id' => 'primaryKey',
                'content' => 'string'
            ),
            array(
                'a_id' => $this->addForeignKey('table_a', 'id')
            )
        );

        $this->addIndex('table_b', 'a_id');
    }


    public function down() {

    }
}


/**
 * @table table_a
 */
class ModelA extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type string
     */
    public $data;

    /**
     * @hasMany ModelB
     */
    public $collectionB;
}


/**
 * @table table_b
 */
class ModelB extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type string
     */
    public $content;

    /**
     * @belongsTo ModelA
     */
    public $relationA;

    /**
     * @foreignKey ModelA
     */
    public $a_id;
}

$iterations = 100;

$db = array(
    'mysql' => array(
        'dsn' => 'mysql:host=localhost;dbname=picomapper',
        'user' => 'root',
        'password' => ''
    ),
    'pgsql' => array(
        'dsn' => 'pgsql:host=localhost;dbname=picomapper',
        'user' => 'picomapper',
        'password' => 'picomapper'
    ),
    'sqlite' => array(
        'dsn' => 'sqlite:/tmp/benchmark.sqlite',
        'user' => '',
        'password' => ''
    )
);

foreach ($db as $driver => $config) {

    \picoMapper\Database::closeInstance();
    \picoMapper\Database::config($config['dsn'], $config['user'], $config['password']);

    /*
    $m = new Version01();
    $m->up();
    $m->execute();
     */

    Chrono::start($driver.'_insertAll');

    for ($i = 0; $i < $iterations; ++$i) {

        $a = new ModelA();
        $a->data = 'testa';
        $a->collectionB[] = new ModelB(array('content' => 'testb'));
        $a->saveAll();
    }

    Chrono::stop($driver.'_insertAll');


    Chrono::start($driver.'_updateAll');

    for ($i = 0; $i < $iterations; ++$i) {

        $a = new ModelA();
        $a->id = $i + 1;
        $a->data = 'testa';
        $a->collectionB[] = new ModelB(array('id' => $i + 1, 'content' => 'testb'));
        $a->saveAll();
    }

    Chrono::stop($driver.'_updateAll');


    Chrono::start($driver.'_count');

    for ($i = 0; $i < $iterations; ++$i) {

        ModelA::count();
    }

    Chrono::stop($driver.'_count');


    Chrono::start($driver.'_fetchOne');

    for ($i = 0; $i < $iterations; ++$i) {

        ModelA::findById($i + 1);
    }

    Chrono::stop($driver.'_fetchOne');


    Chrono::start($driver.'_fetchAll');

    for ($i = 0; $i < $iterations; ++$i) {

        ModelA::findAll();
    }

    Chrono::stop($driver.'_fetchAll');


    Chrono::start($driver.'_hasMany');

    for ($i = 0; $i < $iterations; ++$i) {

        $a = ModelA::findById($i + 1);

        foreach ($a->collectionB as $b) {

            strlen($b->content);
        }
    }

    Chrono::stop($driver.'_hasMany');

    
    Chrono::start($driver.'_belongsTo');

    for ($i = 0; $i < $iterations; ++$i) {

        $b = ModelB::findById($i + 1);
        strlen($b->relationA->data);
    }

    Chrono::stop($driver.'_belongsTo');

}


Chrono::show();

