<?php

require_once 'src/picoMapper.php';


class PostcodeModel extends \picoMapper\Model {

    public $badValue = 'test';
    public $goodValue = 44300;
}


class PostcodeValidatorTest extends PHPUnit_Framework_TestCase {


    public function testMissingProperty() {

        $model = new PostcodeModel();

        $v = new \picoMapper\Validators\PostcodeValidator();
        $rs = $v->execute($model, 'missingValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }


    public function testBadValue() {

        $model = new PostcodeModel();

        $v = new \picoMapper\Validators\PostcodeValidator();
        $rs = $v->execute($model, 'badValue');

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('Invalid postcode')),
            $model->getValidatorErrors()
        );
    }


    public function testGoodValue() {

        $model = new PostcodeModel();

        $v = new \picoMapper\Validators\PostcodeValidator();
        $rs = $v->execute($model, 'goodValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getValidatorErrors());
    }
}

