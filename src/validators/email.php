<?php

namespace picoMapper\Validators;


/**
 * Email validator
 *
 * @author Frédéric Guillot
 */
class EmailValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'Invalid email address';


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

        if (isset($modelInstance->$column) && 
            filter_var($modelInstance->$column, FILTER_VALIDATE_EMAIL) === false) {

            $modelInstance->addError($column, $this->getMessage());

            return false;
        }

        return true;
    }
}

