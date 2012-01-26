<?php

require_once 'src/picoMapper.php';


class SqliteBuilderTest extends PHPUnit_Framework_TestCase {


    public function testIdentifierEscaping() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->escapeIdentifier('blabla');

        $this->assertEquals('"blabla"', $sql);
    }


    public function testInsert() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->insert('blabla', array('truc', 'bidule'));

        $this->assertEquals('INSERT INTO "blabla" ("truc", "bidule") VALUES (?, ?)', $sql);
    }


    public function testUpdate() {

        $builder = new \picoMapper\SqliteBuilder();
        $sql = $builder->update('blabla', array('truc', 'bidule'), 'titi');

        $this->assertEquals('UPDATE "blabla" SET "truc"=?, "bidule"=? WHERE "titi"=?', $sql);
    }


    public function testColumnType() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals('"toto" INTEGER PRIMARY KEY', $builder->columnType('toto', 'primaryKey')); 
        $this->assertEquals('"toto" INTEGER', $builder->columnType('toto', 'integer')); 
        $this->assertEquals('"toto" INTEGER', $builder->columnType('toto', 'boolean')); 
        $this->assertEquals('"toto" TEXT', $builder->columnType('toto', 'string')); 
        $this->assertEquals('"toto" TEXT', $builder->columnType('toto', 'text')); 
        $this->assertEquals('"toto" NUMERIC', $builder->columnType('toto', 'numeric')); 
        $this->assertEquals('"toto" NUMERIC', $builder->columnType('toto', 'decimal')); 
        $this->assertEquals('"toto" REAL', $builder->columnType('toto', 'float')); 
        $this->assertEquals('"toto" BLOB', $builder->columnType('toto', 'binary')); 
        $this->assertEquals('"toto" TEXT', $builder->columnType('toto', 'date')); 
        $this->assertEquals('"toto" TEXT', $builder->columnType('toto', 'datetime')); 
        $this->assertEquals('"toto" TEXT', $builder->columnType('toto', 'time')); 
    }


    public function testCreateTable() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->addTable('blabla', array(
            'cA' => 'primaryKey',
            'cB' => 'text',
            'cC' => 'decimal'
        ));

        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS "blabla" ("cA" INTEGER PRIMARY KEY, "cB" TEXT, "cC" NUMERIC)',
            $sql
        );
    }


    public function testDropTable() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals('DROP TABLE "toto"', $builder->dropTable('toto'));
    }


    public function testForeignKey() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            'REFERENCES "toto"("bla_id")',
            $builder->foreignKey('toto', 'bla_id', false, false)
        );

        $this->assertEquals(
            'REFERENCES "toto"("bla_id") ON UPDATE CASCADE',
            $builder->foreignKey('toto', 'bla_id', false, true)
        );

        $this->assertEquals(
            'REFERENCES "toto"("bla_id") ON DELETE CASCADE',
            $builder->foreignKey('toto', 'bla_id', true, false)
        );

        $this->assertEquals(
            'REFERENCES "toto"("bla_id") ON DELETE CASCADE ON UPDATE CASCADE',
            $builder->foreignKey('toto', 'bla_id', true, true)
        );
    }


    public function testCreateTableWithForeignKeys() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->addTable('blabla', array(
                'cA' => 'primaryKey',
                'cB' => 'text',
                'cC' => 'decimal'
            ),
            array(
                'b_id' => $builder->foreignKey('tableB', 'bid'),
                'c_id' => $builder->foreignKey('tableC', 'cid', true)
            )
        );

        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS "blabla" ("cA" INTEGER PRIMARY KEY, "cB" TEXT, "cC" NUMERIC, "b_id" INTEGER REFERENCES "tableB"("bid"), "c_id" INTEGER REFERENCES "tableC"("cid") ON DELETE CASCADE)',
            $sql
        );
    }


    public function testSelectTable() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->select('titi', 'toto');
        $this->assertEquals('SELECT * FROM "titi" AS "toto"', $sql);

        $sql = $builder->select('titi', 'toto', array('bla', 'truc'));
        $this->assertEquals('SELECT "toto"."bla", "toto"."truc" FROM "titi" AS "toto"', $sql);
    }


    public function testAddJoin() {

        $builder = new \picoMapper\SqliteBuilder();

        $sql = $builder->addJoin('ModelA', 'KeyA', 'TableB', 'ModelB', 'KeyB');
        $this->assertEquals(' LEFT JOIN "TableB" AS "ModelB" ON "ModelA"."KeyA" = "ModelB"."KeyB"', $sql);
    }


    public function testCount() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals('SELECT COUNT(*) FROM "toto" AS "Toto"', $builder->count('toto', 'Toto'));
    }


    public function testLimitOffset() {

        $builder = new \picoMapper\MysqlBuilder();

        $this->assertEquals(' LIMIT ? OFFSET ?', $builder->addLimitOffset());
    }


    public function testAddColumn() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            'ALTER TABLE "toto" ADD COLUMN "titi" INTEGER',
            $builder->addColumn('toto', 'titi', 'integer')
        );
    }


    public function testDropColumn() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            '',
            $builder->dropColumn('toto', 'titi')
        );
    }


    public function testWhere() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(' WHERE test', $builder->addWhere('test'));
    }


    public function testOrder() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(' ORDER BY "titi"."toto" ASC', $builder->addOrder('titi', 'toto'));
        $this->assertEquals(' ORDER BY "titi"."toto" ASC', $builder->addOrder('titi', 'toto', 'ASC'));
        $this->assertEquals(' ORDER BY "titi"."toto" ASC', $builder->addOrder('titi', 'toto', 'bla'));
        $this->assertEquals(' ORDER BY "titi"."toto" DESC', $builder->addOrder('titi', 'toto', 'DESC'));
    }


    public function testAddIndex() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            'CREATE INDEX "toto" ON "tableA"("columnA")',
            $builder->addIndex('toto', 'tableA', 'columnA')
        );
    }


    public function testAddUniqueIndex() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            'CREATE UNIQUE INDEX "toto" ON "tableA"("columnA")',
            $builder->addUnique('toto', 'tableA', 'columnA')
        );

        $this->assertEquals(
            'CREATE UNIQUE INDEX "toto" ON "tableA"("columnA", "columnB")',
            $builder->addUnique('toto', 'tableA', array('columnA', 'columnB'))
        );
    }


    public function testDropIndex() {

        $builder = new \picoMapper\SqliteBuilder();

        $this->assertEquals(
            'DROP INDEX "toto"',
            $builder->dropIndex('toto', 'tableA')
        );
    }

}

