<?php

require_once 'src/picoMapper.php';


class DatabaseTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException PDOException
     */
    public function testNoDsn() {

        \picoMapper\Database::config('');
        \picoMapper\Database::closeInstance();
        \picoMapper\Database::getInstance();
    }  


    public function testGetDriver() {

        $this->assertEquals('', \picoMapper\Database::getDriver());

        \picoMapper\Database::config('sqlite::memory:');

        $this->assertEquals('sqlite', \picoMapper\Database::getDriver());
    }


    public function testGetInstance() {

        \picoMapper\Database::config('sqlite::memory:');
        $db = \picoMapper\Database::getInstance();

        $this->assertInstanceOf('PDO', $db);
    }

}
