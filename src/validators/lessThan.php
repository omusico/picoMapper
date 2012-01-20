<?php

namespace picoMapper\Validators;


/**
 * Less than validator
 *
 * @author Frédéric Guillot
 */
class LessThanValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This field must be less than %s';

    
    /**
     * Execute the validator
     *
     * @access public
     * @param \picoMapper\Model $modelInstance Model instance
     * @param string $column Column name to validate
     * @param array $args Validator parameters
     * @return boolean True if the validation is ok
     */
    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($args[0]) || ! is_numeric($args[0])) {

            throw new \RuntimeException(
                'The first argument is missing for the rule "lessThan" (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && $modelInstance->$column >= $args[0]) {

            $modelInstance->addError($column, sprintf($this->getMessage(), $args[0]));

            return false;
        }

        return true;
    }
}

