<?php

require_once 'src/picoMapper.php';


class Migration01 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('toto', array('truc' => 'string'));
    }

    public function down() {

        $this->dropTable('toto');
    }
}


class Migration02 extends \picoMapper\Migration {

    public function up() {

        $this->addColumn('toto', 'titi', 'integer');
        $this->addIndex('toto', 'titi');
    }

    public function down() {

        $this->dropIndex('toto', 'titi');
    }
}


class Migration03 extends \picoMapper\Migration {

    public function up() {

        $this->addTable(
            'bibi', 
            array('columnA' => 'string'),
            array('foreignKey' => $this->addForeignKey('toto', 'titi', true, true))
        );
    }

    public function down() {

        $this->dropTable('bibi');
    }
}


class Migration04 extends \picoMapper\Migration {

    public function up() {

        $this->addColumn('toto', 'popo', 'integer');
        $this->addUnique('unique_index', 'toto', array('popo', 'titi'));
    }

    public function down() {

        $this->dropUnique('toto', 'unique_index');
    }
}




class MigrationTest extends PHPUnit_Framework_TestCase {


    public function __construct() {

        parent::__construct();

        \picoMapper\Database::closeInstance();
        \picoMapper\Database::config('sqlite::memory:');
    }


    public function testUpCreateTable() {

        $m = new Migration01();
        $m->up();
        $m->execute();

        // Check if the table is created

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'toto'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
    }


    public function testDownDropTable() {

        $m = new Migration01();
        $m->down();
        $m->execute();

        // Check if the table is dropped

        $db = \picoMapper\Database::getInstance();
        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'toto'));
        $rs = $rq->fetch();

        $this->assertEquals(false, $rs);
    }


    public function testUpToMigration02() {

        for ($i = 1; $i <= 2; $i++) {

            $className = 'Migration0'.$i;

            $m = new $className();
            $m->up();
            $m->execute();
        }

        $db = \picoMapper\Database::getInstance();

        // Check if the column is created

        $rq = $db->prepare('INSERT INTO toto VALUES (?, ?)');
        $rq->execute(array('boo', 1));

        $rq = $db->prepare('SELECT titi FROM toto');
        $rq->execute();
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);

        // Check if the index is created

        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('index', 'titi_idx'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
    }


    public function testDownToMigration01() {

        $m = new Migration02();
        $m->down();
        $m->execute();

        $db = \picoMapper\Database::getInstance();

        // Check if the index is removed

        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('index', 'titi_idx'));
        $rs = $rq->fetch();

        $this->assertEquals(false, $rs);
    }


    public function testUpToMigration03() {

        $m = new Migration03();
        $m->up();
        $m->execute();

        $db = \picoMapper\Database::getInstance();

        // Check if the foreign key is created

        $rq = $db->prepare('SELECT sql FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('table', 'bibi'));
        $rs = $rq->fetch();

        $this->assertEquals(
            'CREATE TABLE "bibi" ("columnA" TEXT, "foreignKey" INTEGER REFERENCES "toto"("titi") ON DELETE CASCADE ON UPDATE CASCADE)',
            $rs['sql']
        );
    }


    public function testUpToMigration04() {

        $m = new Migration04();
        $m->up();
        $m->execute();

        $db = \picoMapper\Database::getInstance();

        // Check if the index is created

        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('index', 'unique_index'));
        $rs = $rq->fetch();

        $this->assertNotEquals(false, $rs);
    }


    public function testDownToMigration03() {

        $m = new Migration04();
        $m->down();
        $m->execute();

        $db = \picoMapper\Database::getInstance();

        // Check if the index is created

        $rq = $db->prepare('SELECT name FROM sqlite_master WHERE type=? AND name=?');
        $rq->execute(array('index', 'unique_index'));
        $rs = $rq->fetch();

        $this->assertEquals(false, $rs);
    }

}

