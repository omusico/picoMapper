<?php

namespace picoMapper\Validators;


/**
 * Unique field validator
 *
 * @author Frédéric Guillot
 */
class UniqueValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This field must be unique';


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

        $method = 'countBy'.$column;
        $rs = $modelInstance::$method($modelInstance->$column);

        if ($rs >= 1) {

            $modelInstance->addError($column, $this->getMessage());
            return false;
        }

        return true;
    }
}
