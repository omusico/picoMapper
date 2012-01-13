<?php

namespace picoMapper;


/**
 * Used for lazy loading of relation one-one
 *
 * @author Frédéric Guillot
 */
class ModelProxy {

    /**
     * Instance of the model
     *
     * @access private
     * @var Model
     */
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


    public function initBelongsToRelation() {

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


    public function initHasOneRelation() {

        $originMeta = MetadataStorage::get($this->originModel);
        $relationMeta = MetadataStorage::get($this->relationModel);

        $keyName = $relationMeta->getForeignKey($this->originModel);
        $keyValue = $this->modelInstance->{$originMeta->getPrimaryKey()};

        $className = $this->relationModel;

        $this->instance = $className::Query()
            ->where($this->relationModel.'.'.$keyName.' = ?', $keyValue)
            ->fetchOne();
    }


    public function initInstance() {

        if ($this->instance === null) {

            if ($this->relationType === 'belongsTo') {

                $this->initBelongsToRelation();
            }
            else {

                $this->initHasOneRelation();
            }
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
}

