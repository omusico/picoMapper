<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fguillot.fr
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace picoMapper;


/**
 * Used for lazy loading of relation one-one
 *
 * @author Frédéric Guillot
 */
class ModelProxy {

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
     * Relation type: belongsTo or hasOne
     *
     * @access private
     * @var Model
     */
    private $relationType;


    /**
     * Constructor
     *
     * @access public
     * @param string $originModel Origin model name
     * @param string $relationModel Relation model name
     * @param string $relationType Relation type: belongsTo or hasOne
     * @param \picoMapper\Model $modelInstance Current model instance
     */
    public function __construct($originModel, $relationModel, $relationType, &$modelInstance) {

        $this->originModel = $originModel;
        $this->relationModel = $relationModel;
        $this->relationType = $relationType;
        $this->modelInstance = $modelInstance;
    }


    /**
     * Initialize a belongsTo relation
     *
     * @access public
     */
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


    /**
     * Initialize a hasOne relation
     *
     * @access public
     */
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


    /**
     * Initialize a new instance
     *
     * @access public
     */
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
}

