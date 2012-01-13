<?php

namespace picoMapper;


class CollectionProxy implements \ArrayAccess, \Iterator {

    private $instance = null;
    private $originModel;
    private $relationModel;
    private $modelInstance;


    public function __construct($originModel, $relationModel, &$modelInstance) {

        $this->originModel = $originModel;
        $this->relationModel = $relationModel;
        $this->modelInstance = $modelInstance;
    }


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


    public function initInstance() {

        if ($this->instance === null) {

            $this->initHasManyRelation();
        }
    }


    public function instanceLoaded() {

        return $this->instance !== null ? true : false;
    }


    public function __get($name) {

        $this->initInstance();

        return isset($this->instance->$name) ? $this->instance->$name : null;
    }


    public function __set($name, $value) {

        $this->initInstance();

        if (isset($this->instance->$name)) {

            $this->instance->$name = $value;
        }
    }


    public function __isset($name) {

        $this->initInstance();

        return isset($this->instance->$name);
    }


    public function __call($name, $arguments) {

        $this->initInstance();

        return call_user_func_array(
            array($this->instance, $name),
            $arguments
        );
    }


    public function rewind() {

        $this->initInstance();
        $this->instance->rewind();
    }


    public function current() {

        $this->initInstance();
        return $this->instance->current();
    }


    public function key() {

        $this->initInstance();
        return $this->instance->key();
    }


    public function next() {

        $this->initInstance();
        $this->instance->next();
    }


    public function valid() {

        $this->initInstance();
        return $this->instance->valid();
    }


    public function offsetSet($offset, $value) {

        $this->initInstance();
        $this->instance->offsetSet($offset, $value);
    }


    public function offsetExists($offset) {

        $this->initInstance();
        return $this->instance->offsetExists($offset);
    }


    public function offsetUnset($offset) {

        $this->initInstance();
        $this->instance->offsetUnset($offset);
    }


    public function offsetGet($offset) {

        $this->initInstance();
        return $this->instance->offsetGet($offset);
    }
}

