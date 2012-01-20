<?php

require_once 'src/picoMapper.php';


class MaxLengthModel extends \picoMapper\Model {

    public $badValue = '1234567';
    public $goodValue = '123456';
}


class MaxLengthValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "maxLength" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\MaxLengthValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new MaxLengthModel();

        $v = new \picoMapper\Validators\MaxLengthValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new MaxLengthModel();

        $v = new \picoMapper\Validators\MaxLengthValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field is too long (6 max.)')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new MaxLengthModel();

        $v = new \picoMapper\Validators\MaxLengthValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

