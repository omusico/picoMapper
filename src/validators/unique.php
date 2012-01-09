<?php

namespace picoMapper\Validators;

class UniqueValidator implements \picoMapper\IValidator {

    private $defaultErrorMessage = 'This field must be unique';


    public function execute(&$modelInstance, $column, $args = array()) {

        $method = 'countBy'.$column;
        $rs = $modelInstance::$method($modelInstance->$column);

        if ($rs >= 1) {

            $modelInstance->addError($column, $this->defaultErrorMessage);
            return false;
        }

        return true;
    }
}
