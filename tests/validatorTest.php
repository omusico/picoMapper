<?php

require_once 'src/picoMapper.php';


class ModelVA extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type string
     * @rule required
     */
    public $data;
}


class ValidatorTest extends PHPUnit_Framework_TestCase {


    public function testExecuteRule() {

        $m = new ModelVA();
        $m->data = '1234567891234567';

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertFalse($v->executeRule('maxLength', array(10), 'data'));

        $m = new ModelVA();
        $m->data = 'b';

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertTrue($v->executeRule('maxLength', array(10), 'data'));
    }


    public function testValidateField() {

        $m = new ModelVA();

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertFalse($v->validateField('data'));

        $m->data = 'youpi';

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertTrue($v->validateField('data'));

        $this->assertFalse($v->validateField('data', array('maxLength' => array('1'))));
    }


    public function testValidateAll() {

        $m = new ModelVA();

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertFalse($v->validateAll());

        $this->assertEquals(
            array('data' => array('This field is required')),
            $m->getErrors()
        );

        $m->data = 'youpi';

        $v = new \picoMapper\Validator('ModelVA', $m);
        $this->assertTrue($v->validateAll());
    }
}

