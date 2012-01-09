<?php

namespace picoMapper;


class Query {

    private $model = '';
    private $table = '';
    private $columns = '*';
    private $where = null;
    private $limit = null;
    private $offset = null;
    private $orderCol = null;
    private $orderDir = null;
    private $jointures = null;

    private $parameters = array();

    private $builder = null;
    private $metadata = null;


    public function __construct($model) {

        $this->model = $model;
        $this->builder = Builder::create();
        $this->metadata = MetadataStorage::get($this->model);

        return $this;
    }


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

        if ($row && isset($row['COUNT(*)'])) {

            return (int) $row['COUNT(*)'];
        }

        return 0;
    }


    public function fetchAll() {

        $sql = $this->builder->select(
            $this->metadata->getTable(),
            $this->metadata->getModelName(),
            $this->metadata->getColumns()
        );

        $sql .= $this->jointures;
        if ($this->where) $sql .= $this->builder->addWhere($this->where);
        
        if ($this->offset) {

            $sql .= $this->builder->addOffset();
            $this->parameters[] = $this->offset;
        }

        if ($this->limit) {

            $sql .= $this->builder->addLimit();
            $this->parameters[] = $this->limit;
        }

        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
        $rows = $rq->fetchAll(\PDO::FETCH_ASSOC);

        if ($rows) {

            $results = new Collection();

            foreach ($rows as $row) {

                $results[] = ResultSet::convert($this->model, $row);
            }

            return $results;
        }

        return null;
    }


    public function fetchOne() {
        
        $sql = $this->builder->select(
            $this->metadata->getTable(),
            $this->metadata->getModelName(),
            $this->metadata->getColumns()
        );

        if ($this->where) $sql .= $this->builder->addWhere($this->where);
        $sql .= $this->builder->addLimit();
        $this->parameters[] = 1;
        
        $db = Database::getInstance();

        $rq = $db->prepare($sql);
        $rq->execute($this->parameters);
        $rows = $rq->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($rows[0])) {

            return ResultSet::convert($this->model, $rows[0]);
        }

        return null;
    }


    public function delete() {

    }


    public function join($model) {

        $metadata = MetadataStorage::get($model);

        $this->jointures .= $this->builder->addJoin(
            $this->metadata->getModelName(),
            $this->metadata->getForeignKey($model),
            $metadata->getTable(),
            $metadata->getModelName(),
            $metadata->getPrimaryKey()
        );

        return $this;
    }


    public function where() {

        $args = \func_get_args();
        $nb_args = count($args);

        if ($nb_args >= 1) {

            if ($this->where) {

                $this->where = '('.$this->where.') AND ('.$args[0].')';
            }
            else {

                $this->where = $args[0];
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


    public function asc($name) {

        $this->orderCol = $name;
        $this->orderDir = 'ASC';

        return $this;
    }


    public function limit($limit) {

        if (is_numeric($limit)) $this->limit = $limit;

        return $this;
    }


    public function offset($offset) {

        if (is_numeric($offset)) $this->offset = $offset;

        return $this;
    }
}

