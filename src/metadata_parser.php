<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace picoMapper;


/**
 * Metadata parser
 *
 * @author Frédéric Guillot
 */
class MetadataParser {

    /**
     * Annotations
     *
     * @access private
     * @var array
     */
    private $annotations = array();


    /**
     * Class name to parse
     *
     * @access private
     * @var string
     */
    private $className = null;


    /**
     * Constructor
     *
     * @access public
     * @param string $className Name of the class to parse
     */
    public function __construct($className) {

        $this->className = $className;
    }


    /**
     * Execute the parser
     *
     * @access public
     * @return array Metadata
     */
    public function execute() {
        
        if (! class_exists($this->className)) {

            throw new \RuntimeException('Class "'.$this->className.'" not found');
        }

        $reflection = new \ReflectionClass($this->className);

        $metadata = $this->parseAnnotations($reflection->getDocComment());
        $metadata['class'] = $this->className;

        $metadata['properties'] = array();
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $p) {

            $metadata['properties'][$p->getName()] = $this->parseAnnotations($p->getDocComment());
        }
        
        return $metadata;
    }


    /**
     * Parse annotations
     *
     * @access public
     * @param string $comment Extracted comment bloc
     * @return array Annotations
     */
    public function parseAnnotations($comment) {

        $data = array();

        $lines = explode("\n", $comment);

        foreach ($lines as $line) {

            $line = trim($line);

            foreach ($this->annotations as $annotation) {
                
                if (($p = strpos($line, '@'.$annotation)) !== false) {

                    $value = substr($line, $p + strlen($annotation) + 2);

                    if ($value === false) $value = array();

                    if (isset($data[$annotation])) {

                        if (! is_array($data[$annotation])) {

                            $data[$annotation] = (array) $data[$annotation];
                        }

                        $data[$annotation][] = $value;
                    }
                    else {

                        $data[$annotation] = $value;
                    }
                }
            }
        }

        foreach ($data as &$value) {

            if (is_array($value)) {

                foreach ($value as &$subvalue) {

                    $tmp = explode(' ', $subvalue);

                    if (count($tmp) >= 2) {

                        $subvalue = array();
                        $subvalue[$tmp[0]] = array_slice($tmp, 1);
                    }
                }
            }
            else {

                $tmp = explode(' ', $value);
                
                if (count($tmp) >= 2) {

                    $value = array();
                    $value[$tmp[0]] = array_slice($tmp, 1);
                }
            }
        }

        return $data;
    }


    /**
     * Add annotation to parse, only registred annotations are parsed
     *
     * @access public
     * @param string $identifier Annotation name
     */
    public function registerAnnotation($identifier) {

        $this->annotations[] = $identifier;
    }


    /**
     * Add a list of annotations to parse, only registred annotations are parsed
     *
     * @access public
     * @param array $annotations Annotations name list
     */
    public function registerAnnotations(array $annotations) {

        $this->annotations = array_merge($this->annotations, $annotations);
    }
}

