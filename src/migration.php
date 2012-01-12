<?php

namespace picoMapper;


class Migration {

    private $sql = array();
    private $builder;
    private $db;


    public function __construct() {

        $this->builder = Builder::create();
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

            throw new DatabaseException('Unable to execute this migration: "'.get_called_class().'"');
        }
    }


    public function addTable($table, array $columns, array $foreignKeys = array()) {

        $this->sql[] = $this->builder->createTable($table, $columns, $foreignKeys);
    }


    public function addColumn($table, $column, $type) {

    }


    public function addIndex($table, $column) {

    }


    public function addForeignKey($table, $column, $onDelete = false, $onUpdate = false) {

        return $this->builder->foreignKey($table, $column, $onDelete, $onUpdate);
    }

}

