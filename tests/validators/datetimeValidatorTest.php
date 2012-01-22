<?php

require_once 'src/picoMapper.php';
require_once 'src/validators/datetime.php';

class DatetimeModel extends \picoMapper\Model {

    public $badValue = 10;
    public $goodValue = '30/12/2012';
}


class DatetimeValidatorTest extends PHPUnit_Framework_TestCase {

    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The date format is missing (field "missingValue")
     */
    public function testMissingArgument() {

        $v = new \picoMapper\Validators\DatetimeValidator();
        $v->execute($model, 'missingValue');
    }


    public function testMissingProperty() {

        $model = new DatetimeModel();

        $v = new \picoMapper\Validators\DatetimeValidator();
        $rs = $v->execute($model, 'missingValue', array(6));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }


    public function testBadValue() {

        $model = new DatetimeModel();

        $v = new \picoMapper\Validators\DatetimeValidator();
        $rs = $v->execute($model, 'badValue', array('d/m/Y'));

        $this->assertFalse($rs);
        $this->assertEquals(
            array('badValue' => array('This date must follow this format d/m/Y')),
            $model->getErrors()
        );
    }


    public function testGoodValue() {

        $model = new DatetimeModel();

        $v = new \picoMapper\Validators\DatetimeValidator();
        $rs = $v->execute($model, 'goodValue', array('d/m/Y'));

        $this->assertTrue($rs);
        $this->assertEquals(array(), $model->getErrors());
    }
}

