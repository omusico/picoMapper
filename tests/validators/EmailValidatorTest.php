<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/email.php';


class EmailModel extends \picoMapper\Model {

    public $badValue = 'test';
    public $goodValue = 'titi+machin@localhost';
}


class EmailValidatorTest extends PHPUnit_Framework_TestCase {


    public function testMissingProperty() {

        $model = new EmailModel();

        $v = new \picoMapper\Validators\EmailValidator();
        $rs = $v->execute($model, 'missingValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }


    public function testBadValue() {

        $model = new EmailModel();

        $v = new \picoMapper\Validators\EmailValidator();
        $rs = $v->execute($model, 'badValue');

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('Invalid email address')),
            $model->getErrors()
        );
    }


    public function testGoodValue() {

        $model = new EmailModel();

        $v = new \picoMapper\Validators\EmailValidator();
        $rs = $v->execute($model, 'goodValue');

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }
}

