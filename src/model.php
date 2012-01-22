<?php

namespace picoMapper;


/**
 * Model exception
 *
 * @author Frédéric Guillot
 */
class ModelException extends \Exception {};


/**
 * Model base class
 *
 * @author Frédéric Guillot
 */
class Model {

    /**
     * Validator errors
     *
     * @access protected
     * @var array
     */
    protected $validatorErrors = array();


    /**
     * Validator messages
     *
     * @access public
     * @var array
     */
    protected $validatorMessages = array();


    /**
     * Constructor
     *
     * If there is specified data, properties are filled
     *
     * @access public
     * @param array $data Model data
     */
    public function __construct($data = array()) {

        $metadata = MetadataStorage::get(get_called_class());

        foreach ($metadata->getHasManyRelations() as $property => $model) {

            $this->$property = new Collection();

            if (isset($data[$property]) && is_array($data[$property])) {

                for ($i = 0, $ilen = count($data[$property]); $i < $ilen; $i++) {

                    $this->$property->offsetSet($i, new $model($data[$property][$i]));
                }

                unset($data[$property]);
            }
        }

        foreach ($metadata->getHasOneRelations() as $property => $model) {

            if (isset($data[$property]) && is_array($data[$property])) {

                $this->$property = new $model($data[$property]);
                unset($data[$property]);
            }
        }

        foreach ($metadata->getBelongsToRelations() as $property => $model) {

            if (isset($data[$property]) && is_array($data[$property])) {

                $this->$property = new $model($data[$property]);
                unset($data[$property]);
            }
        }

        foreach ($data as $property => $value) {

            $this->$property = $value;
        }
    }


    /**
     * Add a validator error
     *
     * @access public
     * @param string $column Column name
     * @param string $message Error message
     */
    final public function addError($column, $message) {

        if (! isset($this->validatorErrors[$column])) {

            $this->validatorErrors[$column] = array();
        }

        $this->validatorErrors[$column][] = $message;
    }
    
    
    /**
     * Get validator errors
     *
     * @access public
     */
    final public function getErrors() {

        return $this->validatorErrors;
    }


    /**
     * Static magic helpers for the model
     *
     * Get a query instance: Model::Query()
     * Get a query instance: Model::Find()
     * Find one record by a column name: Model::findBy[Column](value)
     * Fetch all record: Model::findAll()
     * Get the number of records: Model::count()
     * 
     * @access public
     * @static
     * @param string $name Function name
     * @param array $arguments Function arguments
     * @return mixed
     */
    final public static function __callStatic($name, $arguments) {

        $name = strtolower($name);

        if ($name === 'query' || $name === 'find') {

            return new Query(get_called_class());
        }
        else if ($name === 'count') {

            $query = new Query(get_called_class());
            return $query->count();
        }
        else if (strpos($name, 'countby') !== false) {

            $property = substr($name, 7);
            $condition = sprintf('%s.%s = ?', get_called_class(), $property);

            $query = new Query(get_called_class());

            return $query->where($condition, $arguments[0])->count();
        }
        else if (strpos($name, 'find') !== false) {

            $query = new Query(get_called_class());

            if (substr($name, 4, 7) === 'all') {

                return $query->fetchAll();
            }
            else if (substr($name, 4, 2) === 'by') {

                $property = substr($name, 6);

                return $query
                    ->where(sprintf('%s.%s = ?', get_called_class(), $property), $arguments[0])
                    ->fetchOne();
            }
        }
    }


    /**
     * Validate the current model
     *
     * @access public
     * @param boolean $validate Enable or disable the auto validation
     */
    final public function save($validate = true) {

        if ($validate === true && $this->validate() === false) {

            throw new ValidatorException('Validator error');
        }

        $this->beforeSave();

        $p = new Persistence(get_called_class(), $this);
        $p->save();
        
        $this->afterSave();
    }


    /**
     * Save the model and all defined relations
     *
     * @access public
     * @param boolean $validate Enable or disable the auto validation
     * @param boolean $inTransaction True if a DB transaction is already established
     */
    final public function saveAll($validate = true, $inTransaction = false) {

        $p = new Persistence(get_called_class(), $this);
        $p->saveAll($inTransaction, $validate);
    }


    /**
     * Validate the model
     *
     * @access public
     * @return boolean True if everything is ok
     */
    final public function validate() {

        $this->beforeValidate();

        $v = new Validator(get_called_class(), $this, $this->validatorMessages);
        $rs = $v->execute();

        $this->afterValidate();

        return $rs;
    }


    /**
     * Before validate callback
     *
     * @access public
     */
    public function beforeValidate() {

    }


    /**
     * After validate callback
     *
     * @access public
     */
    public function afterValidate() {

    }


    /**
     * Before save callback
     *
     * @access public
     */
    public function beforeSave() {

    }


    /**
     * After save callback
     *
     * @access public
     */
    public function afterSave() {

    }
}

