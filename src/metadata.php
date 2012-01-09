<?php

namespace picoMapper;

class MetadataException extends \Exception {}

class Metadata {

    private $metadata = array();

    private $model = '';
    private $table = '';
    private $primaryKey = '';
    private $columns = array();
    private $columns_types = array();
    private $foreignKeys = array();
    private $belongsToRelations = array();
    private $hasOneRelations = array();
    private $hasManyRelations = array();
    private $rules = array();


    public function __construct(array $metadata) {

        $this->metadata = $metadata;
    }


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


    public function getColumns() {

        if (empty($this->columns) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['type']) || isset($settings['foreignKey'])) {

                    $this->columns[] = $name;
                }
            }
        }

        return $this->columns;
    }


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


    public function getForeignKey($model) {

        if (empty($this->foreignKeys)) $this->getForeignKeys();

        return isset($this->foreignKeys[$model]) ? $this->foreignKeys[$model] : strtolower($model.'_id');
    }


    public function getColumnsRules() {

        if (empty($this->rules) && isset($this->metadata['properties'])) {

            foreach ($this->metadata['properties'] as $name => $settings) {

                if (isset($settings['type'])) {

                }
                else if (isset($settings['foreignKey'])) {

                }
            }
        }

        return $this->rules;
    }
}

