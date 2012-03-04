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
 * Sqlite SQL builder
 */
class SqliteBuilder extends BaseBuilder {

    
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
                $sqlType = 'INTEGER PRIMARY KEY';
                break;

            case 'boolean':
            case 'integer':
                $sqlType = 'INTEGER';
                break;

            case 'decimal':
            case 'numeric':
                $sqlType = 'NUMERIC';
                break;

            case 'float':
                $sqlType = 'REAL';
                break;

            case 'binary':
                $sqlType = 'BLOB';
                break;
        }

        return sprintf('%s %s',
            $this->escapeIdentifier($name),
            $sqlType
        );
    }


    /**
     * Remove a column from a table
     *
     * Sqlite don't support drop column from ALTER TABLE
     * http://www.sqlite.org/faq.html#q11
     *
     * @access public
     * @param string $table Table name
     * @param string $column Column name
     * @return string Generated SQL
     */
    public function dropColumn($table, $column) {

        return '';
    }
}

