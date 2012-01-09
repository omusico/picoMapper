<?php

namespace picoMapper;


class ResultSet {

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


    public static function convertToPhp($type, $value) {

        switch ($type) {

            case 'date':
            case 'datetime':
                return new \Datetime($value);

            case 'primaryKey':
            case 'foreignKey':
            case 'integer':
                return (integer) $value;

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

