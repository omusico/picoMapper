<?php

namespace picoMapper\Validators;

class EmailValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'Invalid email address';


    public function execute(&$modelInstance, $column, $args = array()) {

        if (isset($modelInstance->$column) && 
            filter_var($modelInstance->$column, FILTER_VALIDATE_EMAIL) === false) {

            $modelInstance->addError($column, $this->defaultErrorMessage);

            return false;
        }

        return true;
    }
}

