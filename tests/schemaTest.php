<?php

require_once 'src/picoMapper.php';


class SchemaTest extends PHPUnit_Framework_TestCase {


    public function testCreateTable() {

        \picoMapper\Database::config('sqlite::memory:');

        $s = new \picoMapper\Schema();
        $s->createVersionTable();
    }


    public function testFetchLastVersionFromDirectory() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        
        mkdir($directory);
        touch($directory.DIRECTORY_SEPARATOR.'20111028.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111030.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111023.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111025.php');
    
        \picoMapper\Database::config('sqlite::memory:');
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();
        $this->assertEquals('20111030', $s->getLastVersionFromDirectory());
        
        unlink($directory.DIRECTORY_SEPARATOR.'20111028.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111030.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111023.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111025.php');
        rmdir($directory);
    }


    public function testNoSqlFile() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        mkdir($directory);
        
        \picoMapper\Database::config('sqlite::memory:');
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();
        $this->assertEquals('', $s->getLastVersionFromDirectory());

        rmdir($directory);
    }

/*
    public function testCompareVersion() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        
        mkdir($directory);
        file_put_contents($directory.DIRECTORY_SEPARATOR.'20111028.php', 'bla');
    
        \picoMapper\Database::config('sqlite::memory:');
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();
        $s->createVersionTable();
        $s->compareVersion();
        $this->assertEquals('20111028', $s->getLastVersionFromDatabase());

        file_put_contents($directory.DIRECTORY_SEPARATOR.'20121028.php', 'bla');

        $s->compareVersion();
        $this->assertEquals('20121028', $s->getLastVersionFromDatabase());

        file_put_contents($directory.DIRECTORY_SEPARATOR.'20081028.php', 'bla');

        $s->compareVersion();
        $this->assertEquals('20121028', $s->getLastVersionFromDatabase());
        
        unlink($directory.DIRECTORY_SEPARATOR.'20081028.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111028.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20121028.php');
        rmdir($directory);
    }*/
}

