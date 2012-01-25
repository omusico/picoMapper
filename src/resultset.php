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
 * Convert a SQL resultset to a model instance
 *
 * @author Frédéric Guillot
 */
class ResultSet {


    /**
     * Convert a SQL row to a model
     *
     * @access public
     * @static
     * @param string $name Model name
     * @param array $row SQL resultset
     * @return object Model instance
     */
    public static function convert($name, $row) {

        $model = self::initialize($name);

        $metadata = MetadataStorage::get($name);
        $types = $metadata->getColumnsTypes();

        foreach ($row as $column => $value) {

            $model->$column = self::convertToPhp($types[$column], $value);
        }

        return $model;
    }


    /**
     * Convert a value to a PHP type
     *
     * @access public
     * @static
     * @param string $type Model definition type
     * @param string $value Value to convert
     * @return mixed PHP typed value
     */
    public static function convertToPhp($type, $value) {

        switch ($type) {

            case 'date':
            case 'datetime':
            case 'time':
                return new \Datetime($value);

            case 'primaryKey':
            case 'foreignKey':
            case 'integer':
                return (integer) $value;

            case 'boolean':
                return (boolean) $value;

            case 'decimal':
            case 'numeric':
            case 'real':
            case 'float':
                return (float) $value;
        
            default:
                return $value;
        }
    }

    
    /**
     * Initialize a model instance and setup proxy for lazy loading of relations
     *
     * @access public
     * @static
     * @param string $name Model name
     * @return object Model instance
     */
    public static function initialize($name) {

        $model = new $name();
        $metadata = MetadataStorage::get($name);

        foreach ($metadata->getBelongsToRelations() as $property => $relationModel) {

            $model->$property = new ModelProxy($name, $relationModel, 'belongsTo', $model);
        }

        foreach ($metadata->getHasOneRelations() as $property => $relationModel) {

            $model->$property = new ModelProxy($name, $relationModel, 'hasOne', $model);
        }

        foreach ($metadata->getHasManyRelations() as $property => $relationModel) {

            $model->$property = new CollectionProxy($name, $relationModel, $model);
        }

        return $model;
    }
}

