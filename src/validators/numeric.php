<?php

namespace picoMapper\Validators;


/**
 * Numeric validator
 *
 * @author Frédéric Guillot
 */
class NumericValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This field must be numeric';


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

        if (isset($modelInstance->$column) && ! is_numeric($modelInstance->$column)) {

            $modelInstance->addError($column, $this->getMessage());

            return false;
        }

        return true;
    }
}

