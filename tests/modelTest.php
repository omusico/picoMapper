<?php

require_once 'src/picoMapper.php';


class Model0 extends \picoMapper\Model {}


class Model1 extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;


    public $data;
}


class Model2 extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;


    /**
     * @type string
     */
    public $data;
}


class ModelTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::config('sqlite::memory:');

        $builder = \picoMapper\BuilderFactory::getInstance();

        for ($i = 1; $i <= 2; $i++) {

            $sql = $builder->addTable('model'.$i, array('id' => 'primaryKey', 'data' => 'string'));
            \picoMapper\Database::getInstance()->exec($sql);
        }
    }


    /**
     * @expectedException picoMapper\MetadataException
     * @expectedExceptionMessage No primary key defined for the model "Model0"
     */
    public function testInsertNoPrimaryKey() {

        $model = new Model0();
        $model->truc = 'kk';
        $model->save();
    }


    public function testInsertNoColumnDefined() {

        $model = new Model1();
        $model->data = 'blabla';
        $model->save();

        $this->assertEquals(null, $model->id);
    }


    public function testInsert() {

        $model = new Model2();
        $model->data = 'blabla';
        $model->save();

        $this->assertEquals(1, $model->id);

        $tmp = Model2::findById($model->id);

        $this->assertEquals($model->data, $tmp->data);
    }


    public function testUpdate() {

        $model = new Model2();
        $model->data = 'huhu';
        $model->save();

        $tmp = Model2::findById($model->id);

        $this->assertEquals($model->data, $tmp->data);

        $tmp->data = 'hihi';
        $tmp->save();

        $this->assertEquals($model->id, $tmp->id);

        $tmp1 = Model2::findById($model->id);

        $this->assertEquals($model->id, $tmp1->id);
        $this->assertEquals($tmp->data, $tmp1->data);
    }


    public function testFindBy() {

        $v = Model2::findById(2);
        $this->assertEquals('hihi', $v->data);

        $v = Model2::findByData('blabla');
        $this->assertEquals(1, $v->id);
    }


    public function testFindAll() {

        $v = Model1::findAll();
        $this->assertEquals(0, $v->count());

        $v = Model2::findAll();
        $this->assertEquals(2, $v->count());
        $this->assertEquals('hihi', $v[1]->data);
    }


    public function testCountAll() {

        $v = Model2::count();
        $this->assertEquals(2, $v);
    }


    public function testWhere() {

        $v = Model2::find()
            ->where('Model2.data = ? AND Model2.id = ?', 'hihi', 2)
            ->fetchOne();

        $this->assertEquals(2, $v->id);


        $v = Model2::find()
            ->where('Model2.data != ?', 'hihi')
            ->fetchOne();

        $this->assertEquals(1, $v->id);


        $v = Model2::find()
            ->where('Model2.id >= ?', 3)
            ->fetchOne();

        $this->assertEquals(null, $v);


        $v = Model2::find()
            ->where('Model2.id < ?', 3)
            ->fetchAll();

        $this->assertEquals(2, $v->count());

        $v = Model2::find()
            ->where('Model2.id < ?', 3)
            ->limit(1)
            ->fetchAll();

        $this->assertEquals(1, $v->count());
        $this->assertEquals(1, $v[0]->id);

        $v = Model2::find()
            ->where('Model2.id < ?', 3)
            ->limit(1)
            ->offset(1)
            ->fetchAll();

        $this->assertEquals(1, $v->count());
        $this->assertEquals(2, $v[0]->id);

        $v = Model2::find()
            ->where('Model2.id < ?', 3)
            ->limit(1)
            ->offset(0)
            ->desc('id')
            ->fetchAll();

        $this->assertEquals(1, $v->count());
        $this->assertEquals(2, $v[0]->id);

        $v = Model2::find()
            ->where('Model2.id < ?', 3)
            ->limit(1)
            ->offset(1)
            ->asc('id')
            ->fetchAll();

        $this->assertEquals(1, $v->count());
        $this->assertEquals(2, $v[0]->id);
    }
}

