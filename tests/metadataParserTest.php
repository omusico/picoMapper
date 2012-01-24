<?php

require_once 'src/picoMapper.php';

/**
 * Parse moi
 *
 * @machin truc
 * @author moi
 */
class ParseMoi {

    /**
     * Toto
     *
     * @access public
     * @foreignKey machin
     */
    public $toto;

    /**
     * Hey
     *
     * @access privat
     * @foreignKey Hey
     */
    private $hey;

    /**
     * Var
     *
     * @access public
     * @foreignkey Bla
     */
    public $var;
}


class MetadataParserTest extends PHPUnit_Framework_TestCase {


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Class "toto" not found
     */
    public function testExecuteNoDefinedClass() {

        $p = new \picoMapper\MetadataParser('toto');
        $p->execute();
    }


    public function testExecuteDefinedClass() {

        $p = new \picoMapper\MetadataParser('parsemoi');
        $metadata = $p->execute();

        $this->assertEquals(
            array(
                'class' => 'parsemoi',
                'properties' => array('toto' => array(), 'var' => array())
            ), 
            $metadata
        );

        $p = new \picoMapper\MetadataParser('ParseMoi');
        $p->registerAnnotations(array('author', 'foreignKey'));
        $metadata = $p->execute();

        $this->assertEquals(
            array(
                'class' => 'ParseMoi',
                'author' => 'moi',
                'properties' => array(
                    'toto' => array('foreignKey' => 'machin'),
                    'var' => array()
                )
            ),
            $metadata
        );
    }


    public function testParseAnnotationsNothingRegistred() {

        $p = new \picoMapper\MetadataParser('toto');

        $comment = <<<'EOD'
        /**
    * 
 */
EOD;

        $this->assertEquals(
            array(),
            $p->parseAnnotations($comment)
        );
    }


    public function testParseAnnotationsOneRegistred() {

        $p = new \picoMapper\MetadataParser('toto');
        $p->registerAnnotation('toto');

        $comment = <<<'EOD'
        /**
         * 
    * @titi
 */
EOD;

        $this->assertEquals(
            array(),
            $p->parseAnnotations($comment)
        );

        $comment = <<<'EOD'
        /**
         * 
    * @toto value
 */
EOD;

        $this->assertEquals(
            array('toto' => 'value'),
            $p->parseAnnotations($comment)
        );

        $comment = <<<'EOD'
        /**
         * @param string $bla Hey
         * @toto value p1 p2
        */
EOD;

        $this->assertEquals(
            array('toto' => array('value' => array('p1', 'p2'))),
            $p->parseAnnotations($comment)
        );

        $comment = <<<'EOD'
        /**
         * @param string $bla Hey
         * @toto
        */
EOD;

        $this->assertEquals(
            array('toto' => array()),
            $p->parseAnnotations($comment)
        );

        $comment = <<<'EOD'
        /**
         * @param string $bla Hey
         * toto
        */
EOD;

        $this->assertEquals(
            array(),
            $p->parseAnnotations($comment)
        );
    }


    public function testAnnotationsManyRegistered() {

        $p = new \picoMapper\MetadataParser('toto');
        $p->registerAnnotation('toto');
        $p->registerAnnotation('truc');
        $p->registerAnnotations(array('toi', 'moi'));

        $comment = <<<'EOD'
        /**
         * @truc machin
         * @toi hey h1, h3
         * @moi
         * @param string $bla Hey
         * @toto value p1 p2
        */
EOD;

        $this->assertEquals(
            array(
                'toto' => array('value' => array('p1', 'p2')),
                'truc' => 'machin',
                'toi' => array('hey' => array('h1,', 'h3')),
                'moi' => array()
            ),
            $p->parseAnnotations($comment)
        );
    }
}

