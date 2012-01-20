<?php

namespace picoMapper\Validators;

class RequiredValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This field is required';


    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($modelInstance->$column) ||
            $modelInstance->$column === null ||
            $modelInstance->$column === '') {

            $modelInstance->addError($column, $this->defaultErrorMessage);

            return false;
        }

        return true;
    }
}

