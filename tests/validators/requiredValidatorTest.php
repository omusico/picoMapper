<?php

require_once 'src/picoMapper.php';


class RequiredModel extends \picoMapper\Model {

    public $goodValue = 123;
}


class RequiredValidatorTest extends PHPUnit_Framework_TestCase {


    public function testMissingProperty() {

        $model = new RequiredModel();

        $v = new \picoMapper\Validators\RequiredValidator();
        $rs = $v->execute($model, 'missingValue');

        $this->assertFalse($rs);
        $this->assertEquals(
            array('missingValue' => array('This field is required')),
            $model->validationErrors
        );
    }


    public function testGoodValue() {

        $model = new RequiredModel();

        $v = new \picoMapper\Validators\RequiredValidator();
        $rs = $v->execute($model, 'goodValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->validationErrors);
    }
}

