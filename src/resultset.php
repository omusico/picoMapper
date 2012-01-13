<?php

namespace picoMapper;


/**
 * Convert sql resultset to a model instance
 *
 * @author Frédéric Guillot
 */
class ResultSet {


    /**
     * Convert a SQL row to a model
     *
     * @access public
     * @static
     * @param string $modelName Model name
     * @param array $row SQL resultset
     * @return object Model instance
     */
    public static function convert($modelName, $row) {

        $model = new $modelName();
        $model->setupProxy();

        $metadata = MetadataStorage::get($modelName);
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
}

