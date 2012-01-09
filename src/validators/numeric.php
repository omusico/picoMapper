<?php

namespace picoMapper\Validators;

class NumericValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This field must be numeric';


    public function execute(&$modelInstance, $column, $args = array()) {

        if (isset($modelInstance->$column) && ! is_numeric($modelInstance->$column)) {

            $modelInstance->addError($column, $this->defaultErrorMessage);

            return false;
        }

        return true;
    }
}

