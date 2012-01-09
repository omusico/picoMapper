<?php

namespace picoMapper\Validators;

class LessThanValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This field must be less than %s';

    
    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($args[0]) || ! is_numeric($args[0])) {

            throw new \RuntimeException(
                'The first argument is missing for the rule "lessThan" (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && $modelInstance->$column >= $args[0]) {

            $modelInstance->addError($column, sprintf($this->defaultErrorMessage, $args[0]));

            return false;
        }

        return true;
    }
}

