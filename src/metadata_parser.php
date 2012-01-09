<?php

namespace picoMapper;


class MetadataParser {

    private $annotations = array();
    private $exclude_methods = array();
    private $className = null;

  
    public function __construct($className) {

        $this->className = $className;
    }


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
        
        $metadata['methods'] = array();
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $m) {

            if (! in_array($m->getName(), $this->exclude_methods)) {

                $metadata['methods'][$m->getName()] = $this->parseAnnotations($m->getDocComment());
                $metadata['methods'][$m->getName()]['parameters'] = array();

                $method = $reflection->getMethod($m->getName());

                foreach ($method->getParameters() as $p) {

                    $metadata['methods'][$m->getName()]['parameters'][] = $p->getName();
                }
            }
        }

        return $metadata;
    }


    public function parseAnnotations($comment) {

        $data = array();

        $lines = explode("\n", $comment);

        foreach ($lines as $line) {

            $line = trim($line);

            foreach ($this->annotations as $annotation) {
                
                if (($p = strpos($line, '@'.$annotation)) !== false) {

                    $value = substr($line, $p + strlen($annotation) + 2);

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


    public function registerAnnotation($identifier) {

        $this->annotations[] = $identifier;
    }


    public function registerAnnotations(array $annotations) {

        $this->annotations = array_merge($this->annotations, $annotations);
    }


    public function excludeMethods(array $methods) {

        $this->exclude_methods = $methods;
    }
}

