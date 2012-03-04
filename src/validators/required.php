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
 * Required validator
 *
 * @author Frédéric Guillot
 */
class RequiredValidator extends \picoMapper\BaseValidator {

    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = 'This field is required';


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

        if (! isset($modelInstance->$column) ||
            $modelInstance->$column === null ||
            $modelInstance->$column === '') {

            $modelInstance->addError($column, $this->getMessage());

            return false;
        }

        return true;
    }
}

