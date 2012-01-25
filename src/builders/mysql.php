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
 * Mysql SQL builder
 */
class MysqlBuilder extends BaseBuilder {


    /**
     * Escape a MySQL identifier (table name and column name)
     * 
     * Mysql use backticks `
     *
     * @access public
     * @param string $str String to escape
     * @return string The escaped string
     */
    public function escapeIdentifier($str) {

        return sprintf('`%s`', $str);
    }


    /**
     * Generate a CREATE TABLE command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param array $columns A key/value pair with the column name and type
     * @param array $foreignKeys List of foreign keys
     * @param array $indexes List of index to create
     * @return string Generated SQL
     */
    public function addTable($table, array $columns, array $foreignKeys = array(), array $indexes = array()) {

        $lines = array();

        foreach ($columns as $name => $type) {

            $lines[] = $this->columnType($name, $type);
        }

        foreach ($foreignKeys as $column => $definition) {

            $lines[] = sprintf('%s INT', $this->escapeIdentifier($column));
            $lines[] = sprintf('FOREIGN KEY (%s) %s',
                $this->escapeIdentifier($column),
                $definition
            );
        }

        foreach ($indexes as $column) {

            $lines[] = sprintf('INDEX %s (%s)',
                $this->escapeIdentifier($column.'_idx'),
                $this->escapeIdentifier($column)
            );
        }

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
            $this->escapeIdentifier($table),
            implode(', ', $lines)
        );

        $sql .= ' ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        return $sql;
    }


    /**
     * Generate a column type for a CREATE TABLE or ALTER TABLE
     *
     * @access public
     * @param string $name Column name
     * @param string $type Column type: integer, primaryKey, decimal, text...
     * @return string Generated SQL
     */
    public function columnType($name, $type) {

        $sqlType = 'TEXT';

        switch ($type) {

            case 'primaryKey':
                $sqlType = sprintf(
                    'INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(%s)',
                    $this->escapeIdentifier($name)
                );
                break;

            case 'boolean':
                $sqlType = 'TINYINT(1)';
                break;

            case 'integer':
                $sqlType = 'INT';
                break;

            case 'decimal':
            case 'numeric':
                $sqlType = 'DECIMAL(10,2)';
                break;

            case 'float':
                $sqlType = 'FLOAT';
                break;

            case 'binary':
                $sqlType = 'BLOB';
                break;

            case 'string':
                $sqlType = 'VARCHAR(255)';
                break;

            case 'date':
                $sqlType = 'DATE';
                break;

            case 'datetime':
                $sqlType = 'DATETIME';
                break;

            case 'time':
                $sqlType = 'TIME';
                break;
        }

        return sprintf('%s %s',
            $this->escapeIdentifier($name),
            $sqlType
        );
    }


    /**
     * Remove an index for a MySQL database
     *
     * @access public
     * @param string $name Index name
     * @param string $table Table name
     * @return string Generated SQL
     */
    public function dropIndex($name, $table) {

        return sprintf(
            'DROP INDEX %s ON %s',
            $this->escapeIdentifier($name),
            $this->escapeIdentifier($table)
        );
    }
}

