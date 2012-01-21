<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/lessThan.php';


class LessThanModel extends \picoMapper\Model {

    public $badValue = 6;
    public $goodValue = 4;
}


class LessThanValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The first argument is missing for the rule "lessThan" (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\LessThanValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new LessThanModel();

        $v = new \picoMapper\Validators\LessThanValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new LessThanModel();

        $v = new \picoMapper\Validators\LessThanValidator();
        $rs = $v->execute($model, 'badValue', array(6));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This field must be less than 6')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new LessThanModel();

        $v = new \picoMapper\Validators\LessThanValidator();
        $rs = $v->execute($model, 'goodValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

