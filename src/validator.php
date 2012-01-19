<?php

namespace picoMapper;


interface IValidator {

    public function execute(&$modelInstance, $column, $args = array());
}


class ValidatorException extends \Exception {}


class Validator {

    private $modelName;
    private $modelInstance;


    public function __construct($modelName, &$modelInstance) {

        $this->modelName = $modelName;
        $this->modelInstance = $modelInstance;
    }


    public function execute() {

        $metadata = MetadataStorage::get($this->modelName);
        $columns_rules = $metadata->getColumnsRules();
        $rs = array();

        foreach ($columns_rules as $column => $rules) {

            foreach ($rules as $rule => $args) {

                $className = '\picoMapper\Validators\\'.$rule.'Validator';
                $validator = new $className();

                $rs[] = $validator->execute($this->modelInstance, $column, $args);
            }
        }

        return ! in_array(false, $rs, true);
    }
}

