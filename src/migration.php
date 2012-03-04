<?php

/*
 * This file is part of picoMapper.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace picoMapper;


/**
 * Handle database migrations
 *
 * @author Frédéric Guillot
 */
abstract class Migration {

    /**
     * List of SQL queries to execute
     * 
     * @access private
     * @var array
     */
    private $sql = array();


    /**
     * Builder instance
     *
     * @access private
     * @var Builder instance
     */
    private $builder;


    /**
     * Database instance
     *
     * @access private
     * @var Database
     */
    private $db;


    /**
     * Set up instructions
     *
     * @access public
     * @abstract
     */
    abstract public function up();


    /**
     * Set down instructions
     *
     * @access public
     * @abstract
     */
    abstract public function down();


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {

        $this->builder = BuilderFactory::getInstance();
        $this->db = Database::getInstance();
    }


    /**
     * Execute all SQL statements in a transaction
     *
     * @access public
     */
    public function execute() {

        try {

            $this->db->beginTransaction();

            foreach ($this->sql as $sql) {

                if ($sql) {

                    $this->db->exec($sql);
                }
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


    /**
     * Add table instruction
     *
     * @access public
     * @param string $table Table name
     * @param array $columns Columns name list
     * @param array $foreignKeys Foreign keys definitions
     */
    public function addTable($table, array $columns, array $foreignKeys = array()) {

        $this->sql[] = $this->builder->addTable($table, $columns, $foreignKeys);
    }


    /**
     * Remove table
     *
     * @access public
     * @param string $table Table name
     */
    public function dropTable($table) {

        $this->sql[] = $this->builder->dropTable($table);
    }


    /**
     * Add column
     *
     * @access public
     * @param string $table Table name
     * @param string $column Column name
     * @param string $type Column type
     */
    public function addColumn($table, $column, $type) {

        $this->sql[] = $this->builder->addColumn(
            $table,
            $column,
            $type);
    }


    /**
     * Remove column
     *
     * @access public
     * @param string $table Table name
     * @param string $column Column name
     */
    public function dropColumn($table, $column) {

        $this->sql[] = $this->builder->dropColumn($table, $column);
    }


    /**
     * Add unique constraint
     *
     * @access public
     * @param string $name Index name
     * @param string $table Table name
     * @param mixed $columns One (string) or many columns name (array)
     */
    public function addUnique($name, $table, $columns) {

        $this->sql[] = $this->builder->addUnique(
            $name,
            $table,
            $columns
        );
    }


    /**
     * Add index
     *
     * The index name is auto generated, ex: column_idx
     *
     * @access public
     * @param string $table Table name
     * @param string $column Column name
     */
    public function addIndex($table, $column) {

        $this->sql[] = $this->builder->addIndex(
            $column.'_idx',
            $table,
            $column);
    }


    /**
     * Remove an unique index
     *
     * @access public
     * @param string $table Table name
     * @param string $name Index name
     */
    public function dropUnique($table, $name) {

        $this->sql[] = $this->builder->dropIndex($name, $table);
    }


    /**
     * Remove an index
     *
     * @access public
     * @param string $table Table name
     * @param string $column Column name
     */
    public function dropIndex($table, $column) {

        $this->sql[] = $this->builder->dropIndex($column.'_idx', $table);
    }


    /**
     * Add a foreign key during a table creation
     *
     * @access public
     * @param string $table Reference table name
     * @param string $column Reference column name inside the reference table
     * @param bool $onDelete Add ON DELETE CASCADE
     * @param bool $onUpdate Add ON UPDATE CASCADE
     * @return string SQL generated
     */
    public function addForeignKey($table, $column, $onDelete = false, $onUpdate = false) {

        return $this->builder->foreignKey($table, $column, $onDelete, $onUpdate);
    }

}

