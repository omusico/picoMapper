<?php

namespace picoMapper;


/**
 * A collection contain a list of model instances
 *
 * A collection of models is returned after a query
 *
 * @author Frédéric Guillot
 */
class Collection implements \ArrayAccess, \Iterator {

    private $position = 0;

    private $container = array();


    public function rewind() {

        $this->position = 0;
    }


    public function current() {

        return $this->container[$this->position];
    }


    public function key() {

        return $this->position;
    }


    public function next() {

        ++$this->position;
    }


    public function valid() {

        return isset($this->container[$this->position]);
    }


    public function offsetSet($offset, $value) {

        if (is_null($offset) || ! is_numeric($offset)) {

            $this->container[] = $value;
        }
        else {

            $this->container[$offset] = $value;
        }
    }


    public function offsetExists($offset) {

        return isset($this->container[$offset]);
    }


    public function offsetUnset($offset) {

        unset($this->container[$offset]);
    }


    public function offsetGet($offset) {

        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    public function toArray() {

        $data = array();

        foreach ($this->container as $object) {

            $data[] = $object->toArray();
        }

        return $data;
    }


    public function count() {

        return count($this->container);
    }
}

