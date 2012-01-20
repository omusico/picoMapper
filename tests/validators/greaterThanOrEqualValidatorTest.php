<?php

require_once 'src/picoMapper.php';


class GreaterThanOrEqualModel extends \picoMapper\Model {

    public $badValue = 4;
    public $goodValue = 6;
}


class GreaterThanOrEqualValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "greaterThanOrEqual" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\GreaterThanOrEqualValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new GreaterThanOrEqualModel();

        $v = new \picoMapper\Validators\GreaterThanOrEqualValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new GreaterThanOrEqualModel();

        $v = new \picoMapper\Validators\GreaterThanOrEqualValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be greater than or equal to 6')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new GreaterThanOrEqualModel();

        $v = new \picoMapper\Validators\GreaterThanOrEqualValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

