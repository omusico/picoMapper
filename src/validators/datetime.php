<?php

namespace picoMapper\Validators;

class DatetimeValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This date must follow this format %s';

    
    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($args[0])) {

            throw new \RuntimeException(
                'The date format is missing (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && 
            date_create_from_format($args[0], $modelInstance->$column) === false) {

            $modelInstance->addError($column, sprintf($this->defaultErrorMessage, $args[0]));

            return false;
        }

        return true;
    }
}
