<?php

namespace picoMapper;


class RelationProxy implements \ArrayAccess, \Iterator {

    private $instance = null;
    private $originModel;
    private $relationModel;
    private $relationType;
    private $modelInstance;


    public function __construct($originModel, $relationModel, $relationType, &$modelInstance) {

        $this->originModel = $originModel;
        $this->relationModel = $relationModel;
        $this->relationType = $relationType;
        $this->modelInstance = $modelInstance;
    }


    private function load() {

        if ($this->instance === null) {

            if ($this->relationType === 'belongsTo') {

                $originMeta = MetadataStorage::get($this->originModel);
                $relationMeta = MetadataStorage::get($this->relationModel);

                $foreignKeyName = $originMeta->getForeignKey($this->relationModel);

                $primaryKeyName = $relationMeta->getPrimaryKey();
                $primaryKeyValue = $this->modelInstance->$foreignKeyName;

                $className = $this->relationModel;
                $this->instance = $className::Query()
                    ->where($this->relationModel.'.'.$primaryKeyName.' = ?', $primaryKeyValue)
                    ->fetchOne();
            }
            else if ($this->relationType === 'hasOne') {

                $originMeta = MetadataStorage::get($this->originModel);
                $relationMeta = MetadataStorage::get($this->relationModel);

                $primaryKeyName = $relationMeta->getPrimaryKey();
                $primaryKeyValue = $this->modelInstance->{$originMeta->getPrimaryKey()};

                $className = $this->relationModel;
                $this->instance = $className::Query()
                    ->where($this->relationModel.'.'.$primaryKeyName.' = ?', $primaryKeyValue)
                    ->fetchOne();
            }
            else if ($this->relationType === 'hasMany') {

                $originMeta = MetadataStorage::get($this->originModel);
                $relationMeta = MetadataStorage::get($this->relationModel);

                $primaryKeyName = $relationMeta->getPrimaryKey();
                $primaryKeyValue = $this->modelInstance->{$originMeta->getPrimaryKey()};

                $className = $this->relationModel;
                $this->instance = $className::Query()
                    ->where($this->relationModel.'.'.$primaryKeyName.' = ?', $primaryKeyValue)
                    ->fetchAll();
            }
        }
    }


    public function __get($name) {

        $this->load();

        return isset($this->instance->$name) ? $this->instance->$name : null;
    }


    public function __set($name, $value) {

        $this->load();

        if (isset($this->instance->$name)) {

            $this->instance->$name = $value;
        }
    }


    public function __call($name, $arguments) {

        $this->load();
        return $this->instance->$name($arguments);
    }


    public function rewind() {

        $this->load();
        $this->instance->rewind();
    }


    public function current() {

        $this->load();
        return $this->instance->current();
    }


    public function key() {

        $this->load();
        return $this->instance->key();
    }


    public function next() {

        $this->load();
        $this->instance->next();
    }


    public function valid() {

        $this->load();
        return $this->instance->valid();
    }


    public function offsetSet($offset, $value) {

        $this->load();
        $this->instance->offsetSet($offset, $value);
    }


    public function offsetExists($offset) {

        $this->load();
        return $this->instance->offsetExists($offset);
    }


    public function offsetUnset($offset) {

        $this->load();
        $this->instance->offsetUnset($offset);
    }


    public function offsetGet($offset) {

        $this->load();

        return $this->instance->offsetGet($offset);
    }
}
