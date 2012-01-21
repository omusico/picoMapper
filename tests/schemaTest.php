<?php

require_once 'src/picoMapper.php';


class SchemaTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::closeInstance();
        \picoMapper\Database::config('sqlite::memory:');
    }


    public function testCreateTable() {

        $s = new \picoMapper\Schema();
        $s->createVersionTable();

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'schema_version'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
    }


    public function testFetchLastVersionFromDatabase() {

        $s = new \picoMapper\Schema();
        $s->createVersionTable();

        $this->assertEquals('', $s->getLastVersionFromDatabase());

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('INSERT INTO schema_version VALUES (?)');
        $rq->execute(array('1234'));

        $this->assertEquals('1234', $s->getLastVersionFromDatabase());
    }


    public function testFetchLastVersionFromDirectory() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        
        mkdir($directory);
        touch($directory.DIRECTORY_SEPARATOR.'20111028.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111030.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111023.php');
        touch($directory.DIRECTORY_SEPARATOR.'20111025.php');
    
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();
        $this->assertEquals('20111030', $s->getLastVersionFromDirectory());
        
        unlink($directory.DIRECTORY_SEPARATOR.'20111028.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111030.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111023.php');
        unlink($directory.DIRECTORY_SEPARATOR.'20111025.php');
        rmdir($directory);
    }


    public function testNoMigrationFile() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        mkdir($directory);
        
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();
        $this->assertEquals('', $s->getLastVersionFromDirectory());

        rmdir($directory);
    }


    public function testProcessMigration() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        mkdir($directory);

        $data = <<<'EOD'
<?php

class Version20111028 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('toto', array('truc' => 'string'));
    }

    public function down() {

        $this->dropTable('toto');
    }
}
EOD;
        
        file_put_contents($directory.DIRECTORY_SEPARATOR.'20111028.php', $data);
    
        \picoMapper\Schema::config($directory);

        $s = new \picoMapper\Schema();

        $this->assertFalse($s->processMigration('bla'));
        $this->assertTrue($s->processMigration('20111028'));

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'toto'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
        
        unlink($directory.DIRECTORY_SEPARATOR.'20111028.php');
        rmdir($directory);
    }


    public function testUpdate() {

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.mktime();
        mkdir($directory);

        $data = <<<'EOD'
        <?php

class Version1234 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('table1', array('truc' => 'string'));
    }

    public function down() {

    }
}
EOD;
        
        file_put_contents($directory.DIRECTORY_SEPARATOR.'1234.php', $data);

        $data = <<<'EOD'
        <?php

class Version1235 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('table2', array('truc' => 'string'));
    }

    public function down() {

    }
}
EOD;
        
        file_put_contents($directory.DIRECTORY_SEPARATOR.'1235.php', $data);

        $data = <<<'EOD'
        <?php

class Version1236 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('table3', array('truc' => 'string'));
    }

    public function down() {

    }
}
EOD;
        
        file_put_contents($directory.DIRECTORY_SEPARATOR.'1236.php', $data);

   
        \picoMapper\Schema::config($directory);
        \picoMapper\Schema::update();

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'table1'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'table2'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'table3'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
        
        unlink($directory.DIRECTORY_SEPARATOR.'1234.php');
        unlink($directory.DIRECTORY_SEPARATOR.'1235.php');
        unlink($directory.DIRECTORY_SEPARATOR.'1236.php');
        rmdir($directory);
    }
}

