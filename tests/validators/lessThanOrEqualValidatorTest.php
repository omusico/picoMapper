<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/lessThanOrEqual.php';


class LessThanOrEqualModel extends \picoMapper\Model {

    public $badValue = 10;
    public $goodValue = 6;
}


class LessThanOrEqualValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "lessThanOrEqual" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\LessThanOrEqualValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new LessThanOrEqualModel();

        $v = new \picoMapper\Validators\LessThanOrEqualValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }


    public function testBadValue() {

        $model = new LessThanOrEqualModel();

        $v = new \picoMapper\Validators\LessThanOrEqualValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be less than or equal to 6')),
            $model->getErrors()
        );
    }


    public function testGoodValue() {

        $model = new LessThanOrEqualModel();

        $v = new \picoMapper\Validators\LessThanOrEqualValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }
}

