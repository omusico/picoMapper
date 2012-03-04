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
 * Postgresql SQL builder
 */
class PostgresBuilder extends BaseBuilder {


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
                $sqlType = 'SERIAL PRIMARY KEY';
                break;

            case 'boolean':
                $sqlType = 'BOOLEAN';
                break;

            case 'integer':
                $sqlType = 'INTEGER';
                break;

            case 'decimal':
            case 'numeric':
                $sqlType = 'DECIMAL(10,2)';
                break;

            case 'float':
                $sqlType = 'REAL';
                break;

            case 'binary':
                $sqlType = 'BYTEA';
                break;

            case 'string':
                $sqlType = 'VARCHAR(255)';
                break;

            case 'date':
                $sqlType = 'DATE';
                break;

            case 'datetime':
                $sqlType = 'TIMESTAMP';
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
}

