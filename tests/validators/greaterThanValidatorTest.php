<?php

require_once 'src/picoMapper.php';


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
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new GreaterThanModel();

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be greater than 6')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new GreaterThanModel();

        $v = new \picoMapper\Validators\GreaterThanValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

