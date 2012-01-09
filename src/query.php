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

        if ($this->orderCol && $this->orderDir) {

            $sql .= $this->builder->addOrder($this->orderCol, $this->orderDir);
        }

        if ($this->limit) {

            $sql .= $this->builder->addLimit();
            $this->parameters[] = $this->limit;
        }

        if ($this->offset) {

            $sql .= $this->builder->addOffset();
            $this->parameters[] = $this->offset;
        }

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


    public function fetchOne() {
        
        $sql = $this->builder->select(
            $this->metadata->getTable(),
            $this->metadata->getModelName(),
            $this->metadata->getColumns()
        );

        $sql .= $this->jointures;

        if ($this->where) $sql .= $this->builder->addWhere($this->where);

        if ($this->orderCol && $this->orderDir) {

            $sql .= $this->builder->addOrder($this->orderCol, $this->orderDir);
        }

        $sql .= $this->builder->addLimit();
        $this->parameters[] = 1;

        if ($this->offset) {

            $sql .= $this->builder->addOffset();
            $this->parameters[] = $this->offset;
        }
        
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


    public function desc($name) {

        $this->orderCol = $name;
        $this->orderDir = 'DESC';

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

