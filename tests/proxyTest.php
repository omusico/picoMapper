<?php

require_once 'src/picoMapper.php';


class Migration201213190700 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('model_a', array('primary_a' => 'primaryKey', 'data' => 'string'));
        $this->addTable('model_b', array('primary_b' => 'primaryKey', 'content' => 'string', 'model_a_id' => 'intger'));
    }

    public function down() {

    }
}


/**
 * @table model_a
 */
class ModelA extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_a;

    /**
     * @type string
     */
    public $data;

    /**
     * @hasOne ModelB
     */
    public $model_b;

    /**
     * @hasMany ModelB
     */
    public $collection_b;
}


/**
 * @table model_b
 */
class ModelB extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_b;

    /**
     * @type string
     */
    public $content;

    /**
     * @belongsTo ModelA
     */
    public $model_a;

    /**
     * @foreignKey ModelA
     */
    public $model_a_id;
}


class ProxyTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::closeInstance();
        \picoMapper\Database::config('sqlite::memory:');

        $m = new Migration201213190700();
        $m->up();
        $m->execute();
    }


    public function testModelProxy() {

        $ma = new ModelA();
        $ma->data = 'toto';
        $ma->save();

        $mb = new ModelB();
        $mb->content = 'titi';
        $mb->model_a = $ma;
        $mb->save();

        $rs = ModelA::findByPrimary_A(1);

        $this->assertEquals($ma->data, $rs->data);
        $this->assertFalse($rs->model_b->instanceLoaded());

        $this->assertInstanceOf('\picoMapper\ModelProxy', $rs->model_b);
        $this->assertEquals($mb->content, $rs->model_b->content);
        $this->assertTrue($rs->model_b->instanceLoaded());
        $this->assertInstanceOf('\picoMapper\ModelProxy', $rs->model_b->model_a);
        $this->assertEquals($ma->data, $rs->model_b->model_a->data);
    }


    public function testCollectionProxy() {

        $ma = new ModelA();
        $ma->data = 'toto';
        $ma->save();

        for ($i = 0; $i < 5; $i++) {

            $mb = new ModelB();
            $mb->content = '#'.$i;
            $mb->model_a = $ma;
            $mb->save();
        }

        $rs = ModelA::findByPrimary_A(1);

        $this->assertEquals($ma->data, $rs->data);
        $this->assertFalse($rs->collection_b->instanceLoaded());

        $this->assertInstanceOf('\picoMapper\CollectionProxy', $rs->collection_b);

        $this->assertEquals(5, $rs->collection_b->count());
        $this->assertTrue($rs->collection_b->instanceLoaded());

        foreach ($rs->collection_b as $m) {

            $this->assertInstanceOf('\picoMapper\ModelProxy', $m->model_a);
            $this->assertEquals(1, $m->model_a_id);
        }
    }
}

