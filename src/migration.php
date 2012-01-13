<?php

namespace picoMapper;


class Migration {

    private $sql = array();
    private $builder;
    private $db;


    public function __construct() {

        $this->builder = BuilderFactory::getInstance();
        $this->db = Database::getInstance();
    }


    public function up() {

    }


    public function down() {

    }


    public function execute() {

        try {

            $this->db->beginTransaction();

            foreach ($this->sql as $sql) {

                $this->db->exec($sql);
            }

            $this->db->commit();
        }
        catch (\PDOException $e) {

            $this->db->rollback();

            throw new DatabaseException(sprintf(
                'Unable to execute this migration: "%s" (%s)',
                get_called_class(),
                $e->getMessage()
            ));
        }
    }


    public function addTable($table, array $columns, array $foreignKeys = array()) {

        $this->sql[] = $this->builder->addTable($table, $columns, $foreignKeys);
    }


    public function addColumn($table, $column, $type) {

        $this->sql[] = $this->builder->addColumn(
            $table,
            $column,
            $type);
    }


    public function addIndex($table, $column) {

        $this->sql[] = $this->builder->addIndex(
            $column.'_idx',
            $table,
            $column);
    }


    public function addForeignKey($table, $column, $onDelete = false, $onUpdate = false) {

        return $this->builder->foreignKey($table, $column, $onDelete, $onUpdate);
    }

}

