<?php

namespace picoMapper\Validators;

class PostCodeValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'Invalid postcode';


    public function execute(&$modelInstance, $column, $args = array()) {

        if (isset($modelInstance->$column) && 
            (! is_numeric($modelInstance->$column) || strlen($modelInstance->$column) != 5)) {

            $modelInstance->addError($column, $this->defaultErrorMessage);

            return false;
        }

        return true;
    }
}

