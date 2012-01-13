<?php

require_once 'src/picoMapper.php';

use \picoMapper\ResultSet;

class ModelSuperCool1 extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type date
     */
    public $created_at;

    /**
     * @hasOne ModelSuperCool2
     */
    public $cool;
}

class ModelSuperCool2 extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type date
     */
    public $created_at;

    /**
     * @foreignKey ModelSuperCool2
     */
    public $cool_id;
}



class ResultSetTest extends PHPUnit_Framework_TestCase {

    public function testConvertToPhp() {

        $this->assertEquals('bla', ResultSet::convertToPhp('text', 'bla'));

        $this->assertEquals(1, ResultSet::convertToPhp('integer', '1'));
        $this->assertEquals(1, ResultSet::convertToPhp('primaryKey', '1'));
        $this->assertEquals(1, ResultSet::convertToPhp('foreignKey', '1'));

        $this->assertEquals(true, ResultSet::convertToPhp('boolean', '1'));
        $this->assertEquals(false, ResultSet::convertToPhp('boolean', '0'));

        $rs = ResultSet::convertToPhp('date', '2012-10-01');
        $this->assertEquals('2012-10-01', $rs->format('Y-m-d'));

        $rs = ResultSet::convertToPhp('datetime', '2012-10-01 10:00:34');
        $this->assertEquals('2012-10-01 10:00:34', $rs->format('Y-m-d H:i:s'));

        $rs = ResultSet::convertToPhp('time', '10:12');
        $this->assertEquals('10:12', $rs->format('H:i'));

        $this->assertEquals(10.20, ResultSet::convertToPhp('decimal', '10.20'));
        $this->assertEquals(10.20, ResultSet::convertToPhp('numeric', '10.20'));
        $this->assertEquals(10.20, ResultSet::convertToPhp('float', '10.20'));
        $this->assertEquals(10.20, ResultSet::convertToPhp('real', '10.20'));
    }


    public function testConvertModel() {

        $rs = ResultSet::convert('ModelSuperCool1', array('id' => '1', 'created_at' => '2010-12-10'));

        $this->assertInstanceOf('ModelSuperCool1', $rs);
        $this->assertEquals(1, $rs->id);
        $this->assertEquals('2010-12-10', $rs->created_at->format('Y-m-d'));
        $this->assertInstanceOf('\picoMapper\ModelProxy', $rs->cool);
    }
}

