<?php

require_once 'src/picoMapper.php';


class NumericModel extends \picoMapper\Model {

    public $badValue = 'test';
    public $goodValue = 123;
}


class NumericValidatorTest extends PHPUnit_Framework_TestCase {


    public function testMissingProperty() {

        $model = new NumericModel();

        $v = new \picoMapper\Validators\NumericValidator();
        $rs = $v->execute($model, 'missingValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->validationErrors);
    }


    public function testBadValue() {

        $model = new NumericModel();

        $v = new \picoMapper\Validators\NumericValidator();
        $rs = $v->execute($model, 'badValue');

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be numeric')),
            $model->validationErrors
        );
    }


    public function testGoodValue() {

        $model = new NumericModel();

        $v = new \picoMapper\Validators\NumericValidator();
        $rs = $v->execute($model, 'goodValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->validationErrors);
    }
}

