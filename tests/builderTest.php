<?php

require_once 'src/picoMapper.php';


class BuilderTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unsupported driver
     */
    public function testUnknown() {

        \picoMapper\Builder::create('blabla');
    }


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unsupported driver
     */
    public function testUnkownFromDriver() {

        \picoMapper\Database::config('');
        \picoMapper\Builder::create();
    }


    public function testFromDriver() {

        \picoMapper\Database::config('sqlite::memory:');

        $r = \picoMapper\Builder::create();
        $this->assertInstanceOf('\picoMapper\SqliteBuilder', $r);
    }
    
    
    public function testInstanceofSqlite() {

        $r = \picoMapper\Builder::create('sqlite');
        $this->assertInstanceOf('\picoMapper\SqliteBuilder', $r);
    }


    public function testInstanceofPg() {

        $r = \picoMapper\Builder::create('postgres');
        $this->assertInstanceOf('\picoMapper\PostgresBuilder', $r);
    }


    public function testInstanceofMysql() {

        $r = \picoMapper\Builder::create('mysql');
        $this->assertInstanceOf('\picoMapper\MysqlBuilder', $r);
    }
}

