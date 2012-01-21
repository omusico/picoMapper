<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/minLength.php';


class MinLengthModel extends \picoMapper\Model {

    public $badValue = 'test';
    public $goodValue = '123456';
}


class MinLengthValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "minLength" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\MinLengthValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new MinLengthModel();

        $v = new \picoMapper\Validators\MinLengthValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new MinLengthModel();

        $v = new \picoMapper\Validators\MinLengthValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field is too short (6 min.)')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new MinLengthModel();

        $v = new \picoMapper\Validators\MinLengthValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

