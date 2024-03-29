<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/greaterThan.php';


class GreaterThanModel extends \picoMapper\Model {

    public $badValue = 6;
    public $goodValue = 77;
}


class GreaterThanValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "greaterThan" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new GreaterThanModel();

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }


    public function testBadValue() {

        $model = new GreaterThanModel();

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be greater than 6')),
            $model->getErrors()
        );
    }


    public function testGoodValue() {

        $model = new GreaterThanModel();

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }
}

