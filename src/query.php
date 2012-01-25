<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fguillot.fr
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace picoMapper;


/**
 * Query
 *
 * @author Frédéric Guillot
 */
class Query {

    /**
     * Model name
     *
     * @access private
     * @var string
     */
    private $model = '';


    /**
     * Model name
     *
     * @access private
     * @var string
     */
    private $parameters = array();


    /**
     * Builder instance
     *
     * @access private
     * @var \picoMapper\Builder
     */
    private $builder = null;


    /**
     * Metadata instance
     *
     * @access private
     * @var \picoMapper\Metadata
     */
    private $metadata = null;


    /**
     * Order condition
     *
     * @access private
     * @var string
     */
    private $order = '';


    /**
     * Where condition
     *
     * @access private
     * @var string
     */
    private $where = null;


    /**
     * Limit value
     *
     * @access private
     * @var integer
     */
    private $limit = null;


    /**
     * Offset value
     *
     * @access private
     * @var integer
     */
    private $offset = null;


    /**
     * Jointures
     *
     * @access private
     * @var string
     */
    private $jointures = null;


    /**
     * List of joined models
     *
     * @access private
     * @var array
     */
    private $jointures_models = array();


    /**
     * List of fields
     *
     * @access private
     * @var array
     */
    private $fields = array();


    /**
     * Constructor
     *
     * @access public
     * @param string $model Model name
     * @return \picoMapper\Query Current instance
     */
    public function __construct($model) {

        $this->model = $model;
        $this->builder = BuilderFactory::getInstance();
        $this->metadata = MetadataStorage::get($this->model);

        return $this;
    }


    /**
     * Get the parameters for the prepared query
     *
     * @access public
     * @return array parameters
     */
    public function getParameters() {

        return $this->parameters;
    }


    /**
     * Build ORDER BY condition
     *
     * @access public
     * @param string $name Column name
     * @param string $model Model name
     * @param string $direction Direction: ASC or DESC
     */
    public function buildOrderCondition($name, $model, $direction) {

        $this->order = null;

        if ($model !== '') {

            $metadata = MetadataStorage::get($model);
            $columns = $metadata->getColumns();
        }
        else {

            $columns = $this->metadata->getColumns();
            $model = $this->model;
        }

        // Add the condition only if the column is defined for the specified model

        if (in_array($name, $columns)) {

            $this->order = $this->builder->addOrder($model, $name, $direction);
        }
    }


    /**
     * Build WHERE condition
     *
     * PS: All column in the condition must be defined like that: Model.column
     *
     * @access public
     * @param string $condition Condition
     * @return string SQL condition
     */
    public function buildWhereCondition($condition) {

        $elements = explode(' ', $condition);

        foreach ($elements as &$element) {

            if (($pos = strpos($element, '.')) !== false) {

                // Find 'Model.column' => replace with '`Model`.`column`'

                $model = substr($element, 0, $pos);
                $column = substr($element, $pos + 1);

                $element = str_replace(
                    $model.'.'.$column,
                    $this->builder->escapeIdentifier($model).
                    '.'.
                    $this->builder->escapeIdentifier($column),
                    $element
                );

                // Add the jointure if the model is not the current one

                if ($model !== $this->model) {

                    $this->join($model);
                }
            }
        }

        return implode(' ', $elements);
    }


    /**
     * Get fields for a select query
     *
     * @access public
     * @return array Fields list
     */
    public function getFields() {

        if (! empty($this->fields)) {

            foreach ($this->fields as $field) {

                if (($pos = strpos($field, '.')) !== false) {

                    $model = substr($field, 0, $pos);

                    if ($this->model !== $model) {

                        $this->join($model);
                    }
                }
            }

            return $this->fields;
        }
        else {

            return $this->metadata->getColumns();
        }
    }


    /**
     * Build a SELECT query
     *
     * @access public
     * @return string SQL query
     */
    public function buildSelectQuery() {

        $sql = $this->builder->select(
            $this->metadata->getTable(),
            $this->metadata->getModelName(),
            $this->getFields()
        );

        $sql .= $this->jointures;

        if ($this->where) $sql .= $this->builder->addWhere($this->where);
        if ($this->order) $sql .= $this->order;

        if ($this->limit) {

            $sql .= $this->builder->addLimit();
            $this->parameters[] = $this->limit;
        }

        if ($this->offset) {

            $sql .= $this->builder->addOffset();
            $this->parameters[] = $this->offset;
        }

        return $sql;
    }


    /**
     * Execute a SELECT COUNT(*) query
     *
     * @access public
     * @return \picoMapper\Collection
     */
    public function count() {

        $sql = $this->builder->count(
            $this->metadata->getTable(),
            $this->metadata->getModelName()
        );

        $sql .= $this->jointures;
        if ($this->where) $sql .= $this->builder->addWhere($this->where);

        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
        $row = $rq->fetch(\PDO::FETCH_ASSOC);

        if ($row !== false && isset($row['COUNT(*)'])) {

            return (int) $row['COUNT(*)'];
        }

        return 0;
    }


    /**
     * Execute a SELECT query
     *
     * @access public
     * @return \picoMapper\Collection
     */
    public function fetchAll() {

        $sql = $this->buildSelectQuery();

        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
        $rows = $rq->fetchAll(\PDO::FETCH_ASSOC);
        
        $results = new Collection();

        if ($rows) {

            foreach ($rows as $row) {

                $results[] = ResultSet::convert($this->model, $row);
            }
        }

        return $results;
    }


    /**
     * Execute a SELECT query and force the limit value at 1
     *
     * @access public
     * @return object Model instance
     */
    public function fetchOne() {

        $this->limit = 1;

        $sql = $this->buildSelectQuery();
        
        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
        $row = $rq->fetch(\PDO::FETCH_ASSOC);

        if ($row !== false) {

            return ResultSet::convert($this->model, $row);
        }

        return null;
    }


    /**
     * Execute a DELETE command
     *
     * Usage: Model::Query()->delete('id = ?', 5);
     *
     * Don't put the model name in the condition
     *
     * @access public
     */
    public function delete() {

        $sql = $this->builder->delete(
            $this->metadata->getTable()
        );

        if (\func_num_args() > 0) {

            $args = \func_get_args();
            $sql .= $this->builder->addWhere($args[0]);
            $this->parameters = array_slice($args, 1);
        }

        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
    }


    /**
     * Select only specified columns
     *
     * @access public
     * @return \picoMapper\Query Current instance
     */
    public function fields() {

        $this->fields = array_merge($this->fields, \func_get_args());
        return $this;
    }


    /**
     * Add a left join
     *
     * @access public
     * @param string $model Model name to join
     * @return \picoMapper\Query Current instance
     */
    public function join($model) {

        if (! in_array($model, $this->jointures_models)) {

            $metadata = MetadataStorage::get($model);

            if ($this->metadata->isBelongsToRelation($model)) {

                $this->jointures .= $this->builder->addJoin(
                    $this->metadata->getModelName(),
                    $this->metadata->getForeignKey($model),
                    $metadata->getTable(),
                    $metadata->getModelName(),
                    $metadata->getPrimaryKey()
                );
            }
            else {

                $this->jointures .= $this->builder->addJoin(
                    $this->metadata->getModelName(),
                    $this->metadata->getPrimaryKey(),
                    $metadata->getTable(),
                    $metadata->getModelName(),
                    $metadata->getForeignKey($this->metadata->getModelName())
                );
            }

            $this->jointures_models[] = $model;
        }

        return $this;
    }


    /**
     * Add a where condition
     *
     * Usage: where('Model.column = ? AND Model.column != ?', 'a', 'b')
     *
     * @access public
     * @return \picoMapper\Query Current instance
     */
    public function where() {

        $args = \func_get_args();
        $nb_args = count($args);

        if ($nb_args >= 1) {

            if ($this->where) {

                $this->where = '('.$this->where.') AND ('.$this->buildWhereCondition($args[0]).')';
            }
            else {

                $this->where = $this->buildWhereCondition($args[0]);
            }
        }

        if ($nb_args > 1) {

            $this->parameters = array_merge(
                $this->parameters,
                array_slice($args, 1)
            );
        }

        return $this;
    }


    /**
     * Add order condition to the query (ASC)
     *
     * @access public
     * @param string $name Column name
     * @param string $model Model name to apply the order command
     * @return \picoMapper\Query Current instance
     */
    public function asc($name, $model = '') {

        $this->buildOrderCondition($name, $model, 'ASC');
        
        return $this;
    }


    /**
     * Add order condition to the query (DESC)
     *
     * @access public
     * @param string $name Column name
     * @param string $model Model name to apply the order command
     * @return \picoMapper\Query Current instance
     */
    public function desc($name, $model = '') {

        $this->buildOrderCondition($name, $model, 'DESC');
        return $this;
    }


    /**
     * Add a limit condition to the query
     *
     * @access public
     * @param integer $limit Limit value
     * @return \picoMapper\Query Current instance
     */
    public function limit($limit) {

        if (is_numeric($limit)) $this->limit = $limit;

        return $this;
    }


    /**
     * Add an offset condition to the query
     *
     * @access public
     * @param integer $offset Offset
     * @return \picoMapper\Query Current instance
     */
    public function offset($offset) {

        if (is_numeric($offset)) $this->offset = $offset;

        return $this;
    }
}

