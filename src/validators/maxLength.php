<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fguillot.fr
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace picoMapper\Validators;


/**
 * Max length validator
 *
 * @author Frédéric Guillot
 */
class MaxLengthValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This field is too long (%s max.)';

    
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
                'The first argument is missing for the rule "maxLength" (field "'.$column.'")'
            );
        }

        if (isset($modelInstance->$column) && mb_strlen($modelInstance->$column) > $args[0]) {

            $modelInstance->addError($column, sprintf($this->getMessage(), $args[0]));

            return false;
        }

        return true;
    }
}

