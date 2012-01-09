<?php

require_once 'src/picoMapper.php';


class SqliteBuilderTest extends PHPUnit_Framework_TestCase {


    public function testInsert() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->insert('blabla', array('truc', 'bidule'));

        $this->assertEquals("INSERT INTO blabla (truc, bidule) VALUES (?, ?)", $sql);
    }


    public function testUpdate() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->update('blabla', array('truc', 'bidule'), 'titi');

        $this->assertEquals("UPDATE blabla SET truc=?, bidule=? WHERE titi=?", $sql);
    }


    public function testCreateTable() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->createTable('blabla', array(
            'cA' => 'primaryKey',
            'cB' => 'text',
            'cC' => 'decimal'
        ));

        $this->assertEquals("CREATE TABLE IF NOT EXISTS blabla (cA INTEGER PRIMARY KEY, cB TEXT, cC TEXT)", $sql);
    }


    public function testSelectTable() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->select('titi', 'toto');
        $this->assertEquals('SELECT * FROM titi AS toto', $sql);

        $sql = $builder->select('titi', 'toto', array('bla', 'truc'));
        $this->assertEquals('SELECT toto.bla, toto.truc FROM titi AS toto', $sql);
    }


    public function testAddJoin() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->addJoin('ModelA', 'KeyA', 'TableB', 'ModelB', 'KeyB');
        $this->assertEquals(' LEFT JOIN TableB AS ModelB ON ModelA.KeyA = ModelB.KeyB', $sql);
    }
}

