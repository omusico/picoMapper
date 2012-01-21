<?php

namespace picoMapper;


/**
 * Base class for different SQL builders
 *
 * Generate SQL to use inside a prepared statement
 *
 * @author Frédéric Guillot
 */
abstract class BaseBuilder {


    /**
     * Escape a SQL identifier (table name and column name)
     *
     * @access public
     * @param string $str String to escape
     * @return string The escaped string
     */
    public function escapeIdentifier($str) {

        return sprintf('"%s"', $str);
    }


    /**
     * Generate a CREATE TABLE command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param array $columns A key/value pair with the column name and type
     * @param array $foreignKeys List of foreign keys
     * @return string Generated SQL
     */
    public function addTable($table, array $columns, array $foreignKeys = array()) {

        $lines = array();

        foreach ($columns as $name => $type) {

            $lines[] = $this->columnType($name, $type);
        }

        foreach ($foreignKeys as $column => $definition) {

            $lines[] = sprintf('%s INTEGER %s',
                $this->escapeIdentifier($column),
                $definition
            );
        }

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
            $this->escapeIdentifier($table),
            implode(', ', $lines)
        );

        return $sql;
    }


    /**
     * Remove a table
     *
     * @access public
     * @param string $table Table name
     * @return string Generated SQL
     */
    public function dropTable($table) {

        return sprintf('DROP TABLE %s', $this->escapeIdentifier($table));
    }


    /**
     * Generate a INSERT command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param array $columns List of columns name
     * @return string Generated SQL
     */
    public function insert($table, array $columns) {

        foreach ($columns as &$value) {

            $value = $this->escapeIdentifier($value);
        }

        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->escapeIdentifier($table),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );

        return $sql;
    }


    /**
     * Generate a UPDATE command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param array $columns List of columns name
     * @param string $key Column name for the WHERE
     * @return string Generated SQL
     */
    public function update($table, array $columns, $key) {

        foreach ($columns as &$value) {

            $value = sprintf('%s=?', $this->escapeIdentifier($value));
        }

        $sql = sprintf('UPDATE %s SET %s WHERE %s=?',
            $this->escapeIdentifier($table),
            implode(', ', $columns),
            $this->escapeIdentifier($key)
        );

        return $sql;
    }
    

    /**
     * Generate a SELECT command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param string $alias Sql alias for the table, here the model name
     * @param array $columns List of columns to fetch, all columns by default
     * @return string Generated SQL
     */
    public function select($table, $alias, $columns = array()) {

        foreach ($columns as &$column) {

            if (($pos = strpos($column, '.')) !== false) {

                $column = sprintf('%s.%s',
                    $this->escapeIdentifier(substr($column, 0, $pos)),
                    $this->escapeIdentifier(substr($column, $pos + 1))
                );
            }
            else {

                $column = sprintf('%s.%s',
                    $this->escapeIdentifier($alias),
                    $this->escapeIdentifier($column)
                );
            }
        }

        if (empty($columns)) {

            $columns[] = '*';
        }

        return sprintf('SELECT %s FROM %s AS %s',
            implode(', ', $columns),
            $this->escapeIdentifier($table),
            $this->escapeIdentifier($alias)
        );
    }


    /**
     * Generate a SELECT COUNT(*) command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @param string $alias Sql alias for the table, here the model name
     * @return string Generated SQL
     */
    public function count($table, $alias) {

        return sprintf('SELECT COUNT(*) FROM %s AS %s',
            $this->escapeIdentifier($table),
            $this->escapeIdentifier($alias)
        );
    }


    /**
     * Generate a DELETE command for a prepared statement
     *
     * @access public
     * @param string $table Table name
     * @return string Generated SQL
     */
    public function delete($table) {

        return sprintf('DELETE FROM %s',
            $this->escapeIdentifier($table)
        );
    }

    
    /**
     * Generate a LEFT JOIN for a SELECT command
     *
     * @access public
     * @param string $currentAlias Alias of the current table
     * @param string $currentKey Foreign key to use from the current table
     * @param string $joinTable Table name to join
     * @param string $joinAlias Alias name for the join table
     * @param string $joinKey Primary key in the join table
     * @return string Generated SQL
     */
    public function addJoin($currentAlias, $currentKey, $joinTable, $joinAlias, $joinKey) {

        return sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s',
            $this->escapeIdentifier($joinTable),
            $this->escapeIdentifier($joinAlias),
            $this->escapeIdentifier($currentAlias),
            $this->escapeIdentifier($currentKey),
            $this->escapeIdentifier($joinAlias),
            $this->escapeIdentifier($joinKey)
        );
    }


    /**
     * Add a LIMIT clause for a SELECT command
     *
     * @access public
     * @return string Generated SQL
     */
    public function addLimit() {

        return ' LIMIT ?';
    }


    /**
     * Add a OFFSET clause for a SELECT command
     *
     * @access public
     * @return string Generated SQL
     */
    public function addOffset() {

        return ' OFFSET ?';
    }


    /**
     * Add a WHERE condition for a SELECT command
     *
     * @access public
     * @param string $where SQL generated condition
     * @return string Generated SQL
     */
    public function addWhere($where) {

        return sprintf(' WHERE %s', $where);
    }


    /**
     * Add a ORDER clause for a SELECT command
     *
     * @access public
     * @param string $alias Alias
     * @param string $column Column name
     * @param string $direction ASC or DESC
     * @return string Generated SQL
     */
    public function addOrder($alias, $column, $direction = 'ASC') {

        if ($direction !== 'ASC' && $direction !== 'DESC') {

            $direction = 'ASC';
        }

        return sprintf(' ORDER BY %s.%s %s',
            $this->escapeIdentifier($alias),
            $this->escapeIdentifier($column),
            $direction
        );
    }


    /**
     * Generate a foreign key reference for a CREATE TABLE
     *
     * @access public
     * @param string $table Referenced table
     * @param string $name Referenced column name
     * @param bool $onDelete Add ON DELETE CASCADE
     * @param bool $onUpdate Add ON UPDATE CASCADE
     * @return string Generated SQL
     */
    public function foreignKey($table, $column, $onDelete = false, $onUpdate = false) {

        $on = '';

        if ($onDelete) {

            $on .= ' ON DELETE CASCADE';
        }
        
        if ($onUpdate) {

            $on .= ' ON UPDATE CASCADE';
        }

        return sprintf(
            'REFERENCES %s(%s)%s',
            $this->escapeIdentifier($table),
            $this->escapeIdentifier($column),
            $on
        );
    }


    /**
     * Add a column to a table
     *
     * @access public
     * @param string $table Table name
     * @param string $name Column name
     * @param string $type Column type
     * @return string Generated SQL
     */
    public function addColumn($table, $name, $type) {

        return sprintf(
            'ALTER TABLE %s ADD COLUMN %s',
            $this->escapeIdentifier($table),
            $this->columnType($name, $type)
        );
    }


    /**
     * Remove a column from a table
     *
     * @access public
     * @param string $table Table name
     * @param string $name Column name
     * @return string Generated SQL
     */
    public function dropColumn($table, $column) {

        return sprintf(
            'ALTER TABLE %s DROP COLUMN %s',
            $this->escapeIdentifier($table),
            $this->escapeIdentifier($column)
        );
    }


    /**
     * Add an index
     *
     * @access public
     * @param string $name Index name
     * @param string $table Table name
     * @param string $column Column name
     * @return string Generated SQL
     */
    public function addIndex($name, $table, $column) {

        return sprintf(
            'CREATE INDEX %s ON %s(%s)',
            $this->escapeIdentifier($name),
            $this->escapeIdentifier($table),
            $this->escapeIdentifier($column)
        );
    }


    /**
     * Remove an index
     *
     * @access public
     * @param string $name Index name
     * @param string $table Table name
     * @param string $column Column name
     * @return string Generated SQL
     */
    public function dropIndex($name, $table) {

        return sprintf(
            'DROP INDEX %s',
            $this->escapeIdentifier($name)
        );
    }


    /**
     * Add an unique constraint for one or many columns
     *
     * @access public
     * @param string $name Index name
     * @param string $table Table name
     * @param mixed $columns Column name or list of columns name
     * @return string Generated SQL
     */
    public function addUnique($name, $table, $columns) {

        if (is_array($columns)) {

            foreach ($columns as &$value) {

                $value = $this->escapeIdentifier($value);
            }

            $columns = implode(', ', $columns);
        }
        else {

            $columns = $this->escapeIdentifier($columns);
        }

        return sprintf(
            'CREATE UNIQUE INDEX %s ON %s(%s)',
            $this->escapeIdentifier($name),
            $this->escapeIdentifier($table),
            $columns
        );
    }
}

