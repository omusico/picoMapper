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

        $metadata = \picoMapper\MetadataStorage::get(get_class($modelInstance));
        $primaryKey = $metadata->getPrimaryKey();
        $modelName = $metadata->getModelName();
        $query = new \picoMapper\Query($modelName); 

        if ($modelInstance->$primaryKey) {

            // On update, we exclude the current record

            $rs = $query
                ->where(
                    sprintf('%s.%s = ? AND %s.%s != ?', $modelName, $column, $modelName, $primaryKey),
                    $modelInstance->$column, $modelInstance->$primaryKey
                )
                ->count();
        }
        else {

            $rs = $query
                ->where(sprintf('%s.%s = ?', $modelName, $column), $modelInstance->$column)
                ->count();
        }

        if ($rs >= 1) {

            $modelInstance->addError($column, $this->getMessage());
            return false;
        }

        return true;
    }
}
