<?php

namespace picoMapper;


/**
 * Exception validator
 *
 * @author Frédéric Guillot
 */
class ValidatorException extends \Exception {}


/**
 * Base class for independant validator
 *
 * @author Frédéric Guillot
 */
abstract class BaseValidator {

    /**
     * Custom error message
     *
     * @access private
     * @var string
     */
    private $customMessage = '';


    /**
     * Default error message
     *
     * @access protected
     * @var string
     */
    protected $defaultMessage = '';


    /**
     * Execute the validator
     *
     * @access public
     * @abstract
     * @param \picoMapper\Model $modelInstance Model instance
     * @param string $column Column name to validate
     * @param array $args Validator parameters
     * @return boolean True if the validation is ok
     */
    abstract public function execute(&$modelInstance, $column, $args = array());


    /**
     * Constuctor
     *
     * @access public
     * @param string $message Set a custom error message
     */
    public function __construct($message = '') {

        $this->customMessage = $message;
    }


    /**
     * Get the error message
     *
     * @access public
     * @return string Error message
     */
    public function getMessage() {

        if ($this->customMessage !== '') {

            return $this->customMessage;
        }

        return $this->defaultMessage;
    }
}


/**
 * Validator, execute all validators thought a model
 *
 * @author Frédéric Guillot
 */
class Validator {

    /**
     * Model name
     *
     * @access private
     * @var string
     */
    private $modelName;


    /**
     * Model instance
     *
     * @access private
     * @var \picoMapper\Model
     */
    private $modelInstance;


    /**
     * Custom error messages
     *
     * @access private
     * @var array
     */
    private $modelMessage;


    /**
     * Construct
     *
     * @access public
     * @param string $name Model name
     * @param \picoMapper\Model $model Model instance
     * @param array $messages Custom error messages for validators
     */
    public function __construct($name, &$model, $messages = array()) {

        $this->modelName = $name;
        $this->modelInstance = $model;
        $this->modelMessages = $messages;
    }


    /**
     * Execute all validators according to defined rules
     *
     * @access public
     * @return boolean True if everything is ok
     */
    public function execute() {

        $metadata = MetadataStorage::get($this->modelName);
        $columns_rules = $metadata->getColumnsRules();
        $results = array();
        $directories = array(
            getcwd().DIRECTORY_SEPARATOR.'validators',
            __DIR__.DIRECTORY_SEPARATOR.'validators'
        );

        foreach ($columns_rules as $column => $rules) {

            foreach ($rules as $rule => $args) {

                $className = __NAMESPACE__.'\Validators\\'.$rule.'Validator';

                if (! class_exists($className)) {

                    foreach ($directories as $directory) {

                        $filename = $directory.DIRECTORY_SEPARATOR.$rule.'.php';

                        if (file_exists($filename)) {

                            require $filename;
                        }
                    }
                }

                if (isset($this->modelMessages[$rule])) {

                    $validator = new $className($this->modelMessages[$rule]);
                }
                else {

                    $validator = new $className();
                }

                $result = $validator->execute($this->modelInstance, $column, $args);

                $results[] = $result;

                if ($result === false) break;
            }
        }

        return ! in_array(false, $results, true);
    }
}

