<?php

namespace picoMapper;


class Builder {

    public static function create($driver = '') {

        if ($driver === '') {

            $driver = Database::getDriver();
        }

        switch($driver) {

            case 'sqlite':
                return new SqliteBuilder();

            case 'postgres':
                return new PostgresBuilder();

            case 'mysql':
                return new MysqlBuilder();

            default:
                throw new \RuntimeException('Unsupported driver');
        }
    }
}


abstract class SqlBuilder {

    public function insert($table, $columns) {
        
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );

        return $sql;
    }


    public function update($table, $columns, $key) {

        $cols = array();

        foreach ($columns as $name) {

            $cols[] = $name.'=?';
        }

        $sql = sprintf('UPDATE %s SET %s WHERE %s=?',
            $table,
            implode(', ', $cols),
            $key
        );

        return $sql;
    }


    public function createTable($table, $columns) {

        $cols = array();

        foreach ($columns as $name => $type) {

            $cols[] = $this->columnToSql($name, $type);
        }

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
            $table,
            implode(', ', $cols)
        );

        return $sql;
    }


    public function select($table, $alias, $columns = '*') {

        if (is_array($columns)) {

            foreach ($columns as &$column) {

                $column = sprintf('%s.%s', $alias, $column);
            }
        }
        else {

            $columns = array('*');
        }

        return sprintf('SELECT %s FROM %s AS %s',
            implode(', ', $columns),
            $table,
            $alias
        );
    }


    public function count($table, $alias) {

        return sprintf('SELECT COUNT(*) FROM %s AS %s',
            $table,
            $alias
        );
    }


    public function addJoin($currentAlias, $currentKey, $joinTable, $joinAlias, $joinKey) {

        return sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s',
            $joinTable,
            $joinAlias,
            $currentAlias,
            $currentKey,
            $joinAlias,
            $joinKey
        );
    }


    public function addLimit() {

        return ' LIMIT ?';
    }


    public function addOffset() {

        return ' OFFSET ?';
    }


    public function addWhere($where) {

        return sprintf(' WHERE %s', $where);
    }


    public function addOrder($column, $direction = 'ASC') {

        return sprintf(' ORDER BY %s %s', $column, $direction);
    }
}


class SqliteBuilder extends SqlBuilder {

    public function columnToSql($name, $type) {

        switch ($type) {

            case 'primaryKey':
                return $name.' INTEGER PRIMARY KEY';

            case 'integer':
                return $name.' INTEGER';

            case 'numeric':
                return $name.' NUMERIC';

            case 'float':
                return $name.' REAL';

            case 'binary':
                return $name.' BLOB';

            default:
                return $name.' TEXT';
        }
    }
}


class PostgresBuilder extends SqlBuilder {

    public function columnToSql($name, $settings = array()) {
    }
}


class MysqlBuilder extends SqlBuilder {

    public function columnToSql($name, $settings = array()) {
    }
}
