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
    }
}

