<?php

require_once 'src/picoMapper.php';


class BuilderTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unsupported driver
     */
    public function testUnknown() {

        \picoMapper\BuilderFactory::getInstance('blabla');
    }


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unsupported driver
     */
    public function testUnkownFromDriver() {

        \picoMapper\Database::config('');
        \picoMapper\BuilderFactory::getInstance();
    }


    public function testFromDriver() {

        \picoMapper\Database::config('sqlite::memory:');

        $r = \picoMapper\BuilderFactory::getInstance();
        $this->assertInstanceOf('\picoMapper\SqliteBuilder', $r);
    }
    
    
    public function testInstanceofSqlite() {

        $r = \picoMapper\BuilderFactory::getInstance('sqlite');
        $this->assertInstanceOf('\picoMapper\SqliteBuilder', $r);
    }


    public function testInstanceofPg() {

        $r = \picoMapper\BuilderFactory::getInstance('postgres');
        $this->assertInstanceOf('\picoMapper\PostgresBuilder', $r);
    }


    public function testInstanceofMysql() {

        $r = \picoMapper\BuilderFactory::getInstance('mysql');
        $this->assertInstanceOf('\picoMapper\MysqlBuilder', $r);
    }
}

