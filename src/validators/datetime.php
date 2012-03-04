<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
