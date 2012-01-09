<?php

namespace picoMapper\Validators;

class MaxLengthValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This field is too long (%s max.)';

    
    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($args[0]) || ! is_numeric($args[0])) {

            throw new \RuntimeException(
                'The first argument is missing for the rule "maxLength" (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && mb_strlen($modelInstance->$column) > $args[0]) {

            $modelInstance->addError($column, sprintf($this->defaultErrorMessage, $args[0]));

            return false;
        }

        return true;
    }
}

