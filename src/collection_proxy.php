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
 * Collection proxy
 *
 * Load relations on-demand (lazy loading)
 *
 * @author Frédéric Guillot
 */
class CollectionProxy implements \ArrayAccess, \Iterator {

    /**
     * Instance of the relation model
     *
     * @access private
     * @var \picoMapper\Model
     */
    private $instance = null;


    /**
     * Model name of the source
     *
     * @access private
     * @var string
     */
    private $originModel;


    /**
     * Model name of the relation
     *
     * @access private
     * @var string
     */
    private $relationModel;


    /**
     * Instance of the base model
     *
     * @access private
     * @var \picoMapper\Model
     */
    private $modelInstance;


    /**
     * Constructor
     *
     * @access public
     * @param string $originModel Origin model name
     * @param string $relationModel Relation model name
     * @param \picoMapper\Model $modelInstance Current model instance
     */
    public function __construct($originModel, $relationModel, &$modelInstance) {

        $this->originModel = $originModel;
        $this->relationModel = $relationModel;
        $this->modelInstance = $modelInstance;
    }


    /**
     * Initialize a hasMany relation
     *
     * @access public
     */
    public function initHasManyRelation() {

        $originMeta = MetadataStorage::get($this->originModel);
        $relationMeta = MetadataStorage::get($this->relationModel);

        $keyName = $relationMeta->getForeignKey($this->originModel);
        $keyValue = $this->modelInstance->{$originMeta->getPrimaryKey()};

        $className = $this->relationModel;
        $this->instance = $className::Query()
            ->where($this->relationModel.'.'.$keyName.' = ?', $keyValue)
            ->fetchAll();
    }


    /**
     * Initialize a new instance
     *
     * @access public
     */
    public function initInstance() {

        if ($this->instance === null) {

            $this->initHasManyRelation();
        }
    }


    /**
     * Check if the proxified instance is loaded
     *
     * @access public
     * @return boolean True if the instance is loaded
     */
    public function instanceLoaded() {

        return $this->instance !== null ? true : false;
    }


    /**
     * Get a property value
     *
     * @access public
     * @param string $name Property name
     * @return mixed
     */
    public function __get($name) {

        $this->initInstance();

        return isset($this->instance->$name) ? $this->instance->$name : null;
    }


    /**
     * Set a new property to the proxified class
     *
     * @access public
     * @param string $name Property name
     * @param mixed $value Value
     */
    public function __set($name, $value) {

        $this->initInstance();

        if (isset($this->instance->$name)) {

            $this->instance->$name = $value;
        }
    }


    /**
     * Check if property exists inside the proxified class
     *
     * @access public
     * @param string $name Property name
     * @return boolean True if exists
     */
    public function __isset($name) {

        $this->initInstance();

        return isset($this->instance->$name);
    }


    /**
     * Call all unknown methods to the proxified class
     *
     * @access public
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed
     */
    public function __call($name, $arguments) {

        $this->initInstance();

        return call_user_func_array(
            array($this->instance, $name),
            $arguments
        );
    }


    /**
     * Rewind
     *
     * @access public
     */
    public function rewind() {

        $this->initInstance();
        $this->instance->rewind();
    }


    /**
     * Current
     *
     * @access public
     * @return \picoMapper\Model
     */
    public function current() {

        $this->initInstance();
        return $this->instance->current();
    }


    /**
     * Key
     *
     * @access public
     * @return integer
     */
    public function key() {

        $this->initInstance();
        return $this->instance->key();
    }


    /**
     * Next
     *
     * @access public
     */
    public function next() {

        $this->initInstance();
        $this->instance->next();
    }


    /**
     * Valid
     *
     * @access public
     */
    public function valid() {

        $this->initInstance();
        return $this->instance->valid();
    }


    /**
     * Add a model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @param \picoMapper\Model $value Model instance
     */
    public function offsetSet($offset, $value) {

        $this->initInstance();
        $this->instance->offsetSet($offset, $value);
    }


    /**
     * Check if there is a model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @return boolean True if exists
     */
    public function offsetExists($offset) {

        $this->initInstance();
        return $this->instance->offsetExists($offset);
    }


    /**
     * Remove a model inside the container
     *
     * @access public
     * @param integer $offset Offset
     */
    public function offsetUnset($offset) {

        $this->initInstance();
        $this->instance->offsetUnset($offset);
    }


    /**
     * Get the model instance at the specified offset
     *
     * @access public
     * @param integer $offset Offset
     * @return \picoMapper\Model
     */
    public function offsetGet($offset) {

        $this->initInstance();
        return $this->instance->offsetGet($offset);
    }
}

