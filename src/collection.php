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
 * A collection contain a list of model instances
 *
 * A collection of models is returned after a query
 *
 * @author Frédéric Guillot
 */
class Collection implements \ArrayAccess, \Iterator {

    /**
     * Current position inside the container
     *
     * @access private
     * @var integer
     */
    private $position = 0;


    /**
     * Model container
     *
     * @access private
     * @var array
     */
    private $container = array();


    /**
     * Rewind
     *
     * @access public
     */
    public function rewind() {

        $this->position = 0;
    }


    /**
     * Current
     *
     * @access public
     * @return \picoMapper\Model
     */
    public function current() {

        return $this->container[$this->position];
    }


    /**
     * Key
     *
     * @access public
     * @return integer
     */
    public function key() {

        return $this->position;
    }


    /**
     * Next
     *
     * @access public
     */
    public function next() {

        ++$this->position;
    }


    /**
     * Valid
     *
     * @access public
     */
    public function valid() {

        return isset($this->container[$this->position]);
    }


    /**
     * Add a model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @param \picoMapper\Model $value Model instance
     */
    public function offsetSet($offset, $value) {

        if (is_null($offset) || ! is_numeric($offset)) {

            $this->container[] = $value;
        }
        else {

            $this->container[$offset] = $value;
        }
    }


    /**
     * Check if there is a model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @return boolean True if exists
     */
    public function offsetExists($offset) {

        return isset($this->container[$offset]);
    }


    /**
     * Remove a model inside the container
     *
     * @access public
     * @param integer $offset Offset
     */
    public function offsetUnset($offset) {

        unset($this->container[$offset]);
    }


    /**
     * Get the model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @return \picoMapper\Model
     */
    public function offsetGet($offset) {

        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    /**
     * Get the collection as an array
     *
     * @access public
     * @param boolean $recursive Fetch or not relations
     * @return array
     */
    public function toArray($recursive = true) {

        $data = array();

        foreach ($this->container as $object) {

            $data[] = $object->toArray($recursive);
        }

        return $data;
    }


    /**
     * Get the number of elements inside the container
     *
     * @access public
     * @return integer Number of elements
     */
    public function count() {

        return count($this->container);
    }
}

