<?php

namespace picoMapper;


class ModelException extends \Exception {};


class Model {

    private $metadata = null;
    public $validationErrors = array();


    public function __construct($data = array()) {

        $this->metadata = MetadataStorage::get(get_called_class());

        foreach ($this->metadata->getHasManyRelations() as $property => $model) {

            $this->$property = new Collection();

            if (isset($data[$property]) && is_array($data[$property])) {

                for ($i = 0, $ilen = count($data[$property]); $i < $ilen; $i++) {

                    $this->$property->offsetSet($i, new $model($data[$property][$i]));
                }

                unset($data[$property]);
            }
        }

        foreach ($this->metadata->getHasOneRelations() as $property => $model) {

            if (isset($data[$property]) && is_array($data[$property])) {

                $this->$property = new $model($data[$property]);
                unset($data[$property]);
            }
        }

        foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

            if (isset($data[$property]) && is_array($data[$property])) {

                $this->$property = new $model($data[$property]);
                unset($data[$property]);
            }
        }

        foreach ($data as $property => $value) {

            $this->$property = $value;
        }
    }


    final public function addError($column, $message) {

        if (! isset($this->validationErrors[$column])) {

            $this->validationErrors[$column] = array();
        }

        $this->validationErrors[$column][] = $message;
    }


    final public function setupProxy() {

        foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

            $this->$property = new RelationProxy(get_called_class(), $model, 'belongsTo', $this);
        }

        foreach ($this->metadata->getHasOneRelations() as $property => $model) {

            $this->$property = new RelationProxy(get_called_class(), $model, 'hasOne', $this);
        }

        foreach ($this->metadata->getHasManyRelations() as $property => $model) {

            $this->$property = new RelationProxy(get_called_class(), $model, 'hasMany', $this);
        }
    }


    final public static function __callStatic($name, $arguments) {

        $name = strtolower($name);

        if ($name === 'query' || $name === 'find') {

            return new Query(get_called_class());
        }
        else if (substr($name, 0, 4) == 'find') {

            $query = new Query(get_called_class());

            if (substr($name, 4, 7) == 'all') {

                return $query->fetchAll();
            }
            else if (substr($name, 4, 2) == 'by') {

                $property = substr($name, 6);

                return $query
                    ->where(sprintf('%s.%s = ?', get_called_class(), $property), $arguments[0])
                    ->fetchOne();
            }
        }
        else if (substr($name, 0, 5) == 'count') {

            $query = new Query(get_called_class());
            $name = strtolower($name);

            if (substr($name, 5, 7) == 'all') {

                return $query->count();
            }
            else if (substr($name, 5, 2) == 'by') {

                $property = substr($name, 7);

                return $query
                    ->where(sprintf('%s.%s = ?', get_called_class(), $property), $arguments[0])
                    ->count();
            }
        }
    }


    final public function save() {

        if ($this->validate() === false) {

            throw new ValidatorException('Validation error');
        }

        $db = Database::getInstance();
        $builder = Builder::create();
        $primaryKey = $this->metadata->getPrimaryKey();

        foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

            if ($this->$property) {

                $metadata = MetadataStorage::get($model);
                $value = $this->$property->{$metadata->getPrimaryKey()};
                $this->{$this->metadata->getForeignKey($model)} = $value;
            }
        }

        try {

            $values = $this->getValues();

            if (count($values) > 1) {

                if ($this->$primaryKey) {

                    $sql = $builder->update(
                        $this->metadata->getTable(),
                        $this->metadata->getColumns(),
                        $primaryKey
                    );

                    $rq = $db->prepare($sql);

                    $values[] = $this->$primaryKey;

                    $rq->execute($values); 
                }
                else {

                    $sql = $builder->insert(
                        $this->metadata->getTable(),
                        $this->metadata->getColumns()
                    );

                    $rq = $db->prepare($sql);
                    $rq->execute($values);

                    $this->$primaryKey = $db->lastInsertId();
                }
            }
        }
        catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage());
        }
    }


    final public function saveAll($inTransaction = false) {

        $db = Database::getInstance();
        $primaryKey = $this->metadata->getPrimaryKey();

        try {

            if (! $inTransaction) $db->beginTransaction();

            foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

                if ($this->$property) $this->$property->saveAll(true);
            }

            $this->save();

            foreach ($this->metadata->getHasOneRelations() as $property => $model) {

                if ($this->$property) {

                    $metadata = MetadataStorage::get($model);
                    $foreignKey = $metadata->getForeignKey($this->metadata->getModelName());

                    $this->$property->$foreignKey = $this->$primaryKey;
                    $this->$property->saveAll(true);
                }
            }

            foreach ($this->metadata->getHasManyRelations() as $property => $model) {

                if ($this->$property->count() > 0) {

                    $metadata = MetadataStorage::get($model);
                    $foreignKey = $metadata->getForeignKey($this->metadata->getModelName());

                    for ($i = 0, $ilen = $this->$property->count(); $i < $ilen; $i++) {

                        $this->$property->offsetGet($i)->$foreignKey = $this->$primaryKey;
                        $this->$property->offsetGet($i)->saveAll(true);
                    }
                }
            }

            if (! $inTransaction) $db->commit();
        }
        catch (\PDOException $e) {

            $db->rollback();
            throw new DatabaseException($e->getMessage());
        }
    }


    final public function validate() {

        $v = new Validator($this->metadata->getModelName(), $this);
        return $v->execute();
    }


    final public function getValues() {

        $data = array();

        foreach ($this->metadata->getColumns() as $column) {

            $data[] = $this->$column;
        }

        return $data;
    }
}

