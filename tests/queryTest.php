<?php

require_once 'src/picoMapper.php';


class Migration201201211449 extends \picoMapper\Migration {

    public function up() {

        $this->addTable('model_a', array('primary_a' => 'primaryKey', 'data' => 'string'));
        $this->addTable('model_b', array('primary_b' => 'primaryKey', 'content' => 'string', 'model_a_id' => 'integer'));
    }

    public function down() {

    }
}


/**
 * @table model_a
 */
class ModelQA extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_a;

    /**
     * @type string
     */
    public $data;

    /**
     * @hasOne ModelQB
     */
    public $model_b;

    /**
     * @hasMany ModelQB
     */
    public $collection_b;
}


/**
 * @table model_b
 */
class ModelQB extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $primary_b;

    /**
     * @type string
     */
    public $content;

    /**
     * @belongsTo ModelQA
     */
    public $model_a;

    /**
     * @foreignKey ModelQA
     */
    public $model_a_id;
}


class QueryTest extends PHPUnit_Framework_TestCase {


    public function setUp() {

        \picoMapper\Database::closeInstance();
        \picoMapper\Database::config('sqlite::memory:');

        $m = new Migration201201211449();
        $m->up();
        $m->execute();
    }


    public function testBuildFetchAllNoCondition() {

        $q = new \picoMapper\Query('ModelQA');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );
    }


    public function testBuildFetchAllOffset() {

        $q = new \picoMapper\Query('ModelQA');

        $q->offset('r');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->offset(10);
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" OFFSET ?',
            $sql
        );

        $this->assertEquals(
            array(10),
            $q->getParameters()
        );
    }


    public function testBuildFetchAllLimit() {

        $q = new \picoMapper\Query('ModelQA');

        $q->limit('r');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->limit(10);
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" LIMIT ?',
            $sql
        );

        $this->assertEquals(
            array(10),
            $q->getParameters()
        );
    }


    public function testBuildFetchAllOrderAsc() {

        $q = new \picoMapper\Query('ModelQA');

        $q->asc('r');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->asc('data');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" ORDER BY "ModelQA"."data" ASC',
            $sql
        );

        $q->asc('r', 'ModelQB');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->asc('content', 'ModelQB');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" ORDER BY "ModelQB"."content" ASC',
            $sql
        );
    }


    public function testBuildFetchAllOrderDesc() {

        $q = new \picoMapper\Query('ModelQA');

        $q->desc('r');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->desc('data');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" ORDER BY "ModelQA"."data" DESC',
            $sql
        );

        $q->desc('r', 'ModelQB');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA"',
            $sql
        );

        $q->desc('content', 'ModelQB');
        $sql = $q->buildSelectQuery();

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" ORDER BY "ModelQB"."content" DESC',
            $sql
        );
    }


    public function testBuildWhere() {

        $q = new \picoMapper\Query('ModelQA');

        $this->assertEquals(
            'data >= ?',
            $q->buildWhereCondition('data >= ?')
        );

        $this->assertEquals(
            '"ModelQA"."data" >= ?',
            $q->buildWhereCondition('ModelQA.data >= ?')
        );

        $this->assertEquals(
            '"ModelQB"."data" >= ?',
            $q->buildWhereCondition('ModelQB.data >= ?')
        );

        $this->assertEquals(
            '"ModelQB"."data" >= ? AND content=?',
            $q->buildWhereCondition('ModelQB.data >= ? AND content=?')
        );

        $this->assertEquals(
            '"ModelQB"."data" >= ? OR "ModelQA"."machin" LIKE ?',
            $q->buildWhereCondition('ModelQB.data >= ? OR ModelQA.machin LIKE ?')
        );
    }


    public function testBuildFetchAllWithWhere() {

        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQA.data >= ?', 4);

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" WHERE "ModelQA"."data" >= ?',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array(4),
            $q->getParameters()
        );

        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQA.data >= ?', 4)
          ->where('ModelQA.data LIKE ?', 'toto');

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" WHERE ("ModelQA"."data" >= ?) AND ("ModelQA"."data" LIKE ?)',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array(4, 'toto'),
            $q->getParameters()
        );

        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQA.data >= ? OR ModelQA.id = ?', 4, 1)
          ->where('ModelQA.data LIKE ?', 'toto');

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" WHERE ("ModelQA"."data" >= ? OR "ModelQA"."id" = ?) AND ("ModelQA"."data" LIKE ?)',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array(4, 1, 'toto'),
            $q->getParameters()
        );

        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQA.data >= ? OR ModelQA.id = ?', 4, 1)
          ->where('ModelQB.data LIKE ?', 'toto');

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" LEFT JOIN "model_b" AS "ModelQB" ON "ModelQA"."primary_a" = "ModelQB"."model_a_id" WHERE ("ModelQA"."data" >= ? OR "ModelQA"."id" = ?) AND ("ModelQB"."data" LIKE ?)',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array(4, 1, 'toto'),
            $q->getParameters()
        );


        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQB.data >= ? OR ModelQA.data = ? OR ModelQB.data LIKE ?', 'toto', 2, 'titi');

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" LEFT JOIN "model_b" AS "ModelQB" ON "ModelQA"."primary_a" = "ModelQB"."model_a_id" WHERE "ModelQB"."data" >= ? OR "ModelQA"."data" = ? OR "ModelQB"."data" LIKE ?',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array('toto', 2, 'titi'),
            $q->getParameters()
        );


        $q = new \picoMapper\Query('ModelQA');
        $q->where('ModelQB.data >= ? OR ModelQA.data = ? OR ModelQB.content LIKE ?', 'toto', 2, 'titi')
          ->offset(5)
          ->limit(10)
          ->desc('content', 'ModelQB');

        $this->assertEquals(
            'SELECT "ModelQA"."primary_a", "ModelQA"."data" FROM "model_a" AS "ModelQA" LEFT JOIN "model_b" AS "ModelQB" ON "ModelQA"."primary_a" = "ModelQB"."model_a_id" WHERE "ModelQB"."data" >= ? OR "ModelQA"."data" = ? OR "ModelQB"."content" LIKE ? ORDER BY "ModelQB"."content" DESC LIMIT ? OFFSET ?',
            $q->buildSelectQuery()
        );

        $this->assertEquals(
            array('toto', 2, 'titi', 10, 5),
            $q->getParameters()
        );
    }


    public function testFields() {

        $q = new \picoMapper\Query('ModelQA');

        $this->assertEquals(
            array('primary_a', 'data'),
            $q->getFields()
        );

        $q->fields('ModelQA.data', 'ModelQB.content');

        $this->assertEquals(
            array('ModelQA.data', 'ModelQB.content'),
            $q->getFields()
        );

        $q->fields('ModelQA.primary_a');

        $this->assertEquals(
            array('ModelQA.data', 'ModelQB.content', 'ModelQA.primary_a'),
            $q->getFields()
        );


        $q = new \picoMapper\Query('ModelQA');
        $q->fields('ModelQA.data', 'ModelQB.content');

        $this->assertEquals(
            'SELECT "ModelQA"."data", "ModelQB"."content" FROM "model_a" AS "ModelQA" LEFT JOIN "model_b" AS "ModelQB" ON "ModelQA"."primary_a" = "ModelQB"."model_a_id"',
            $q->buildSelectQuery()
        );
    }


    public function testFetchAll() {

        $m = new ModelQA();
        $m->data = 'truc';
        $m->save();

        $rs = ModelQA::Query()
          ->where('ModelQA.data = ?', 'truc')
          ->fetchAll();

        $this->assertInstanceOf('\picoMapper\Collection', $rs);
        $this->assertEquals(1, $rs->count());
        $this->assertEquals('truc', $rs[0]->data);

        $m = new ModelQA();
        $m->data = 'truc';
        $m->save();

        $rs = ModelQA::Query()
          ->where('ModelQA.data = ?', 'truc')
          ->desc('primary_a')
          ->fetchAll();
        
        $this->assertEquals(2, $rs->count());
        $this->assertEquals(2, $rs[0]->primary_a);
        $this->assertEquals(1, $rs[1]->primary_a);
    }


    public function testFetchOne() {

        $m = new ModelQA();
        $m->data = 'truc';
        $m->save();

        $b = new ModelQB();
        $b->content = 'bla';
        $b->model_a = $m;
        $b->save();

        $rs = ModelQA::Query()
          ->fields('data')
          ->where('ModelQB.content = ?', 'bla')
          ->fetchOne();

        $this->assertInstanceOf('ModelQA', $rs);
        $this->assertEquals('truc', $rs->data);
        $this->assertEquals(null, $rs->primary_a);

        $rs = ModelQA::Query()
          ->where('ModelQB.content = ?', 'bla9')
          ->fetchOne();

        $this->assertEquals(null, $rs);
    }


    public function testCount() {

        $m = new ModelQA();
        $m->data = 'truc';
        $m->save();

        $b = new ModelQB();
        $b->content = 'bla';
        $b->model_a = $m;
        $b->save();

        $rs = ModelQA::Query()
          ->fields('data')
          ->where('ModelQB.content = ?', 'bla')
          ->count();

        $this->assertEquals(1, $rs);

        $rs = ModelQA::Query()
          ->where('ModelQB.content = ?', 'bla9')
          ->count();

        $this->assertEquals(0, $rs);
    }


    public function testDelete() {

        for ($i = 0; $i < 10; $i++) {

            $m = new ModelQA();
            $m->data = $i;
            $m->save();
        }

        $rs = ModelQA::Query()->where('data <= ?', 4);
        $this->assertEquals(5, $rs->count());

        ModelQA::Query()->delete('data <= ?', 4);
        $this->assertEquals(5, ModelQA::Query()->count());

        ModelQA::Query()->delete();
        $this->assertEquals(0, ModelQA::Query()->count());
    }
}

