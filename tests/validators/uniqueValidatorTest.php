<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/unique.php';


class UniqueModel extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type string
     * @rule unique
     */
    public $name;
}


class UniqueValidatorTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::config('sqlite::memory:');

        $sql = \picoMapper\BuilderFactory::getInstance()->addTable(
            'uniquemodel',
            array('id' => 'primaryKey', 'name' => 'string')
        );

        \picoMapper\Database::getInstance()->exec($sql);
    }


    public function testSaveFirstValue() {

        $model = new UniqueModel();
        $model->name = 'toto';
        $model->save();

        $this->assertEquals(1, $model->id);
    }


    public function testSaveNotUnique() {

        $model = new UniqueModel();
        $model->name = 'toto';
        
        $v = new \picoMapper\Validators\UniqueValidator();
        $rs = $v->execute($model, 'name');

        $this->assertFalse($rs);
        $this->assertEquals(array('name' => array('This field must be unique')), $model->getErrors());
    }


    public function testSaveUnique() {

        $model = new UniqueModel();
        $model->name = 'titi';
        
        $v = new \picoMapper\Validators\UniqueValidator();
        $rs = $v->execute($model, 'name');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }


    public function testSaveUniqueOnUpdate() {

        $model = new UniqueModel();
        $model->id = 1;
        $model->name = 'toto';
        
        $v = new \picoMapper\Validators\UniqueValidator();
        $rs = $v->execute($model, 'name');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());

        $model = new UniqueModel();
        $model->name = 'toto';
        
        $v = new \picoMapper\Validators\UniqueValidator();
        $rs = $v->execute($model, 'name');

        $this->assertFalse($rs);
        $this->assertEquals(array('name' => array('This field must be unique')), $model->getErrors());
    }
}

