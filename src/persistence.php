<?php

namespace picoMapper;


/**
 * Persisting data to the database
 *
 * @author Frédéric Guillot
 */
class Persistence {

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
     * Constructor
     *
     * @access public
     * @param string $name Model name
     * @param \picoMapper\Model $model Model instance
     */
    public function __construct($name, &$model) {

        $this->model = $model;
        $this->builder = BuilderFactory::getInstance();
        $this->metadata = MetadataStorage::get($name);
        $this->db = Database::getInstance();
    }


    /**
     * Get model data
     *
     * @access public
     * @return array List of values
     */
    public function getValues() {

        $data = array();

        foreach ($this->metadata->getColumns() as $column) {

            $data[] = $this->model->$column;
        }

        return $data;
    }


    /**
     * Save the model, do an UPDATE or INSERT in the database
     *
     * @access public
     */
    public function save() {

        $primaryKey = $this->metadata->getPrimaryKey();

        foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

            if ($this->model->$property !== null) {

                $metadata = MetadataStorage::get($model);
                $value = $this->model->$property->{$metadata->getPrimaryKey()};
                $this->model->{$this->metadata->getForeignKey($model)} = $value;
            }
        }

        try {

            $values = $this->getValues();

            if (count($values) > 1) {

                if ($this->model->$primaryKey) {

                    $sql = $this->builder->update(
                        $this->metadata->getTable(),
                        $this->metadata->getColumns(),
                        $primaryKey
                    );

                    $rq = $this->db->prepare($sql);

                    $values[] = $this->model->$primaryKey;

                    $rq->execute($values); 
                }
                else {

                    $sql = $this->builder->insert(
                        $this->metadata->getTable(),
                        $this->metadata->getColumns()
                    );

                    $rq = $this->db->prepare($sql);
                    $rq->execute($values);

                    $this->model->$primaryKey = $this->db->lastInsertId();
                }
            }
        }
        catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage());
        }
    }


    /**
     * Save the model and all relations
     *
     * @access public
     */
    public function saveAll($inTransaction = false, $validate = true) {

        $primaryKey = $this->metadata->getPrimaryKey();

        try {

            if (! $inTransaction) $this->db->beginTransaction();

            foreach ($this->metadata->getBelongsToRelations() as $property => $model) {

                if ($this->model->$property !== null) $this->model->$property->saveAll($validate, true);
            }

            $this->model->save($validate);

            foreach ($this->metadata->getHasOneRelations() as $property => $model) {

                if ($this->model->$property !== null) {

                    $metadata = MetadataStorage::get($model);
                    $foreignKey = $metadata->getForeignKey($this->metadata->getModelName());

                    $this->model->$property->$foreignKey = $this->model->$primaryKey;
                    $this->model->$property->saveAll($validate, true);
                }
            }

            foreach ($this->metadata->getHasManyRelations() as $property => $model) {

                if ($this->model->$property->count() > 0) {

                    $metadata = MetadataStorage::get($model);
                    $foreignKey = $metadata->getForeignKey($this->metadata->getModelName());

                    for ($i = 0, $ilen = $this->model->$property->count(); $i < $ilen; $i++) {

                        $this->model->$property->offsetGet($i)->$foreignKey = $this->model->$primaryKey;
                        $this->model->$property->offsetGet($i)->saveAll($validate, true);
                    }
                }
            }

            if (! $inTransaction) $this->db->commit();
        }
        catch (\PDOException $e) {

            $this->db->rollback();
            throw new DatabaseException($e->getMessage());
        }
    }
}

