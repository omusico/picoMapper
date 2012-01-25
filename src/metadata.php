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
 * Metadata exception
 *
 * @author Frédéric Guillot
 */
class MetadataException extends \Exception {}


/**
 * Metadata
 *
 * @author Frédéric Guillot
 */
class Metadata {

    /**
     * Metadata
     *
     * @access private
     * @var array
     */
    private $metadata = array();


    /**
     * Model name
     *
     * @access private
     * @var string
     */
    private $model = '';


    /**
     * Table
     *
     * @access private
     * @var array
     */
    private $table = '';


    /**
     * Primary key
     *
     * @access private
     * @var array
     */
    private $primaryKey = '';


    /**
     * Columns
     *
     * @access private
     * @var array
     */
    private $columns = array();


    /**
     * Column typs
     *
     * @access private
     * @var array
     */
    private $columns_types = array();


    /**
     * Foreign keys
     *
     * @access private
     * @var array
     */
    private $foreignKeys = array();


    /**
     * Belongs to relations
     *
     * @access private
     * @var array
     */
    private $belongsToRelations = array();


    /**
     * Has one relations
     *
     * @access private
     * @var array
     */
    private $hasOneRelations = array();


    /**
     * Has many relations
     *
     * @access private
     * @var array
     */
    private $hasManyRelations = array();


    /**
     * Rules
     *
     * @access private
     * @var array
     */
    private $rules = array();


    /**
     * Constructor
     *
     * @access public
     * @param array $metadata Parsed metadata
     */
    public function __construct(array $metadata) {

        $this->metadata = $metadata;
    }


    /**
     * Get model name
     *
     * @access public
     * @return string Model name
     */
    public function getModelName() {

        if (! $this->model) {

            if (isset($this->metadata['class'])) {

                $this->model = $this->metadata['class'];
            }
            else {

                throw new MetadataException('Unable to find the model name');
            }
        }

        return $this->model;
    }


    /**
     * Get table name
     *
     * If there is no defined table name, the returned value is the class name
     *
     * @access public
     * @return string Table name
     */
    public function getTable() {

        if (! $this->table) {

            if (isset($this->metadata['table'])) {

                $this->table = $this->metadata['table'];
            }
            else if (isset($this->metadata['class'])) {

                $this->table = strtolower($this->metadata['class']);
            }
            else {

                throw new MetadataException('Unable to find the table name');
            }
        }

        return $this->table;
    }


    /**
     * Get columns list
     *
     * @access public
     * @param boolean $noPrimaryKey Return or not the primary key
     * @return array columns list
     */
    public function getColumns($noPrimaryKey = false) {

        if (empty($this->columns) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['type']) || isset($settings['foreignKey'])) {

                    $this->columns[] = $name;
                }
            }
        }

        if ($noPrimaryKey === true) {

            $columns = array();

            foreach ($this->columns as $key => $column) {

                if ($column !== $this->getPrimaryKey()) {

                    $columns[] = $column;
                }
            }

            return $columns;
        }

        return $this->columns;
    }


    /**
     * Get columns types
     *
     * @access public
     * @return array Columns types
     */
    public function getColumnsTypes() {

        if (empty($this->columns_types) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['type'])) {

                    $this->columns_types[$name] = $settings['type'];
                }
                else if (isset($settings['foreignKey'])) {

                    $this->columns_types[$name] = 'foreignKey';
                }
            }
        }

        return $this->columns_types;
    }


    /**
     * Get the primary key
     *
     * @access public
     * @return string Primary key
     */
    public function getPrimaryKey() {

        if (! $this->primaryKey && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['type']) && $settings['type'] === 'primaryKey') {

                    $this->primaryKey = $name;
                    break;
                }
            }
        }

        if (! $this->primaryKey) {

            throw new MetadataException(
                sprintf('No primary key defined for the model "%s"', $this->metadata['class'])
            );
        }

        return $this->primaryKey;
    }


    /**
     * Check if the specified model is a belongsTo relation
     *
     * @access public
     * @param string $model Model name
     * @return boolean True if the model is a belongsTo relation
     */
    public function isBelongsToRelation($model) {

        $relations = $this->getBelongsToRelations();

        if (! empty($relations)) {

            if (in_array($model, $relations)) return true;
        }

        return false;
    }


    /**
     * Get belongsTo relations
     *
     * @access public
     * @return array Relations
     */
    public function getBelongsToRelations() {

        if (empty($this->belongsToRelations) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['belongsTo'])) {

                    $this->belongsToRelations[$name] = $settings['belongsTo'];
                }
            }
        }

        return $this->belongsToRelations;
    }


    /**
     * Get hasOne relations
     *
     * @access public
     * @return array Relations
     */
    public function getHasOneRelations() {

        if (empty($this->hasOneRelations) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['hasOne'])) {

                    $this->hasOneRelations[$name] = $settings['hasOne'];
                }
            }
        }

        return $this->hasOneRelations;
    }


    /**
     * Get hasMany relations
     *
     * @access public
     * @return array Relations
     */
    public function getHasManyRelations() {

        if (empty($this->hasManyRelations) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['hasMany'])) {

                    $this->hasManyRelations[$name] = $settings['hasMany'];
                }
            }
        }

        return $this->hasManyRelations;
    }


    /**
     * Get all defined foreign keys
     *
     * @access public
     * @return array Foreign keys
     */
    public function getForeignKeys() {

        if (empty($this->foreignKeys) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['foreignKey'])) {

                    $this->foreignKeys[$settings['foreignKey']] = $name;
                }
            }
        }

        return $this->foreignKeys;
    }


    /**
     * Get foreign key for the specified model
     *
     * If there is no defined foreign key, this method return "modelname_id"
     *
     * @access public
     * @param string $model Model name
     * @return string Foreign key
     */
    public function getForeignKey($model) {

        if (empty($this->foreignKeys)) $this->getForeignKeys();

        return isset($this->foreignKeys[$model]) ? $this->foreignKeys[$model] : strtolower($model.'_id');
    }


    /**
     * Get validator rules for all columns
     *
     * @access public
     * @return array Rules
     */
    public function getColumnsRules() {

        if (empty($this->rules) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (! isset($this->rules[$name])) {

                    $this->rules[$name] = array();
                }

                if (isset($settings['rule'])) {

                    if (is_array($settings['rule'])) {

                        foreach ($settings['rule'] as $key => $value) {

                            if (is_array($value)) {

                                $rule = $key;
                            }
                            else {

                                $rule = $value;
                                $value = array();
                            }

                            switch ($rule) {

                                case '>=':
                                    $rule = 'greaterThanOrEqual';
                                    break;

                                case '<=':
                                    $rule = 'lessThanOrEqual';
                                    break;

                                case '>':
                                    $rule = 'greaterThan';
                                    break;

                                case '<':
                                    $rule = 'lessThan';
                                    break;
                            }

                            $this->rules[$name][$rule] = $value;
                        }
                    }
                    else {

                        $this->rules[$name][$settings['rule']] = array();
                    }
                }
            }
        }

        return $this->rules;
    }
}

