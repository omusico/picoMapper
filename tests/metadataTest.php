<?php

require_once 'src/picoMapper.php';


class MetadataTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException \picoMapper\MetadataException
     * @expectedExceptionMessage Unable to find the model name
     */
    public function testNoModel() {

        $metadata = array();

        $m = new \picoMapper\Metadata($metadata);
        
        $m->getModelName();
    }

    
    public function testModelName() {

        $metadata = array(
            'class' => 'Invoice'
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals('Invoice', $m->getModelName());
    }


    /**
     * @expectedException \picoMapper\MetadataException
     * @expectedExceptionMessage Unable to find the table name
     */
    public function testNoTableNoClass() {

        $metadata = array();

        $m = new \picoMapper\Metadata($metadata);
        
        $m->getTable();
    }


    public function testNoTable() {

        $metadata = array(
            'class' => 'Invoice'
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals('invoice', $m->getTable());
    }


    public function testTable() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices'
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals('invoices', $m->getTable());
    }


    /**
     * @expectedException \picoMapper\MetadataException
     * @expectedExceptionMessage No primary key defined for the model "Invoice"
     */
    public function testNoPrimaryKey() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices'
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $m->getPrimaryKey();
    }


    public function testPrimaryKey() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                 )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals('toto', $m->getPrimaryKey());
    }


    public function testColumns() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'bibi' => array(
                    'type' => 'text'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array('toto', 'bibi'), $m->getColumns());
        $this->assertEquals(array('bibi'), $m->getColumns(true));
    }


    public function testIsBelongsTo() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'belongsTo' => 'ModelB'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertTrue($m->isBelongsToRelation('ModelB'));

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'belongsTo' => 'ModelC'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertFalse($m->isBelongsToRelation('ModelB'));
    }


    public function testGetBelongsToRelations() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'belongsTo' => 'ModelB'
                ),
                'modela' => array(
                    'belongsTo' => 'ModelA'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array('modelb' => 'ModelB', 'modela' => 'ModelA'), $m->getBelongsToRelations());

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasOne' => 'ModelB'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array(), $m->getBelongsToRelations());
    }


    public function testGetHasOneToRelations() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasOne' => 'ModelB'
                ),
                'modela' => array(
                    'hasone' => 'ModelA'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array('modelb' => 'ModelB'), $m->getHasOneRelations());

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasMany' => 'ModelB'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array(), $m->getHasOneRelations());
    }


    public function testGetHasManyRelations() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasMany' => 'ModelB'
                ),
                'modela' => array(
                    'hasMany' => 'ModelA'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array('modelb' => 'ModelB', 'modela' => 'ModelA'), $m->getHasManyRelations());

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasOne' => 'ModelB'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array(), $m->getHasManyRelations());
    }


    public function testGetForeignKeys() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb_id' => array(
                    'foreignKey' => 'ModelB'
                ),
                'modela_id' => array(
                    'foreignKey' => 'ModelA'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array('ModelB' => 'modelb_id', 'ModelA' => 'modela_id'), $m->getForeignKeys());

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb' => array(
                    'hasOne' => 'ModelB'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals(array(), $m->getForeignKeys());
    }


    public function testGetForeignKey() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'modelb_id' => array(
                    'foreignKey' => 'ModelB'
                ),
                'modela_id' => array(
                    'foreignKey' => 'ModelA'
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);
        
        $this->assertEquals('modelb_id', $m->getForeignKey('ModelB'));
        $this->assertEquals('modelc_id', $m->getForeignKey('ModelC'));
    }


    public function testGetColumnsRules() {

        $metadata = array(
            'class' => 'Invoice',
            'table' => 'invoices',
            'properties' => array(
                'toto' => array(
                    'type' => 'primaryKey'
                ),
                'field1' => array(
                    'rule' => 'rule1'
                ),
                'field2' => array(
                    'rule' => array(
                        'rule1',
                        '>' => array(1),
                        '<' => array(1.5),
                        '>=' => array(2),
                        '<=' => array(3),
                        'rule3' => array(3, 4)
                    )
                )
             )
        );

        $m = new \picoMapper\Metadata($metadata);

        $this->assertEquals(array(
                'toto' => array(),
                'field1' => array('rule1' => array()),
                'field2' => array(
                    'rule1' => array(),
                    'greaterThan' => array(1),
                    'lessThan' => array(1.5),
                    'greaterThanOrEqual' => array(2),
                    'lessThanOrEqual' => array(3),
                    'rule3' => array(3, 4)                    
                )
            ),
            $m->getColumnsRules()
        );
    }

}

