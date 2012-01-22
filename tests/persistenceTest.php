<?php

require_once 'src/picoMapper.php';


class Migration201201221538 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('model_a', array('primary_a' => 'primaryKey', 'data' => 'string'));
        $this->addTable('model_b', array('primary_b' => 'primaryKey', 'content' => 'string', 'model_a_id' => 'integer'));
    }

    public function down() {

    }
}


/**
 * @table model_a
 */
class ModelPA extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_a;

    /**
     * @type string
     */
    public $data;

    /**
     * @hasOne ModelPB
     */
    public $model_b;

    /**
     * @hasMany ModelPB
     */
    public $collection_b;
}


/**
 * @table model_b
 */
class ModelPB extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_b;

    /**
     * @type string
     */
    public $content;

    /**
     * @belongsTo ModelPA
     */
    public $model_a;

    /**
     * @foreignKey ModelPA
     */
    public $model_a_id;
}


class PersistenceTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::closeInstance();
        \picoMapper\Database::config('sqlite::memory:');

        $m = new Migration201201221538();
        $m->up();
        $m->execute();
    }


    public function testSave() {

        $a = new ModelPA();
        $a->data = 'truc';

        $p = new \picoMapper\Persistence('ModelPA', $a);
        $p->save();

        $this->assertEquals(1, ModelPA::count());
        $this->assertEquals(1, ModelPA::findByData('truc')->primary_a);
        $this->assertEquals(1, $a->primary_a);

        $b = new ModelPA();
        $b->data = 'truc2';

        $p = new \picoMapper\Persistence('ModelPA', $b);
        $p->save();

        $this->assertEquals(2, ModelPA::count());
        $this->assertEquals(1, ModelPA::findByData('truc')->primary_a);
        $this->assertEquals(2, ModelPA::findByData('truc2')->primary_a);
        $this->assertEquals(1, $a->primary_a);
        $this->assertEquals(2, $b->primary_a);

        $b->data = 'machin';

        $p = new \picoMapper\Persistence('ModelPA', $b);
        $p->save();

        $this->assertEquals(2, ModelPA::count());
        $this->assertEquals(1, ModelPA::findByData('truc')->primary_a);
        $this->assertEquals(2, ModelPA::findByData('machin')->primary_a);
        $this->assertEquals(1, $a->primary_a);
        $this->assertEquals(2, $b->primary_a);
    }


    public function testSaveAllHasOne() {

        $m = new ModelPA();
        $m->data = 'truc';
        $m->model_b = new ModelPB(array('content' => 'bla'));

        $p = new \picoMapper\Persistence('ModelPA', $m);
        $p->saveAll();

        $r = ModelPA::findByPrimary_a(1);

        $this->assertEquals('truc', $r->data);
        $this->assertEquals('bla', $r->model_b->content);
    }


    public function testSaveAllHasMany() {

        $m = new ModelPA();
        $m->data = 'truc';
        $m->collection_b[] = new ModelPB(array('content' => 'bla1'));
        $m->collection_b[] = new ModelPB(array('content' => 'bla2'));

        $p = new \picoMapper\Persistence('ModelPA', $m);
        $p->saveAll();

        $r = ModelPA::findByPrimary_a(1);

        $this->assertEquals('truc', $r->data);

        $this->assertEquals('bla1', $r->collection_b[0]->content);
        $this->assertEquals(1, $r->collection_b[0]->model_a_id);
        $this->assertEquals(1, $r->collection_b[0]->primary_b);

        $this->assertEquals('bla2', $r->collection_b[1]->content);
        $this->assertEquals(1, $r->collection_b[1]->model_a_id);
        $this->assertEquals(2, $r->collection_b[1]->primary_b);
    }


    public function testSaveAllBelongsTo() {

        $m = new ModelPB();
        $m->content = 'bla';
        $m->model_a = new ModelPA(array('data' => 'truc'));
        
        $p = new \picoMapper\Persistence('ModelPB', $m);
        $p->saveAll();

        $r = ModelPB::findByPrimary_b(1);

        $this->assertEquals('bla', $r->content);
        $this->assertEquals(1, $r->model_a->primary_a);
        $this->assertEquals('truc', $r->model_a->data);
        $this->assertEquals(1, $r->model_a_id);
    }
}
