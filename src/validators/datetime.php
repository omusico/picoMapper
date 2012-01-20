<?php

namespace picoMapper\Validators;


/**
 * Datetime validator
 *
 * @author Frédéric Guillot
 */
class DatetimeValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This date must follow this format %s';

    
    public function execute(&$modelInstance, $column, $args = array()) {

        if (! isset($args[0])) {

            throw new \RuntimeException(
                'The date format is missing (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && 
            date_create_from_format($args[0], $modelInstance->$column) === false) {

            $modelInstance->addError($column, sprintf($this->getMessage(), $args[0]));

            return false;
        }

        return true;
    }
}
