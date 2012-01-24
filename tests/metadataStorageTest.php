<?php

require_once 'src/picoMapper.php';

/**
 * Parse moi encore
 *
 * @machin truc
 * @table parsemoi
 */
class ParseMoiEncore {

}


class MetadataStorageTest extends PHPUnit_Framework_TestCase {


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Class "toto" not found
     */
    public function testExecuteNoDefinedClass() {

        \picoMapper\MetadataStorage::get('toto');
    }


    public function testExecuteDefinedClass() {

        $metadata = \picoMapper\MetadataStorage::get('parsemoiencore');
        
        $this->assertInstanceOf('\picoMapper\Metadata', $metadata);
        $this->assertEquals('parsemoi', $metadata->getTable());
    }
}

