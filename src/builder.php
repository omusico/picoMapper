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


    abstract public function foreignKey($table, $column, $onDelete = false, $onUpdate = false);
    abstract public function createTable($table, array $columns, array $foreignKeys = array());

    abstract public function addUnique($name, $table, $columns);
    abstract public function addIndex($name, $table, $column);
    abstract public function addConstraint($table, $name, $constraint);
}


class SqliteBuilder extends SqlBuilder {

    
    public function createTable($table, array $columns, array $foreignKeys = array()) {

        $lines = array();

        foreach ($columns as $name => $type) {

            $lines[] = $this->columnToSql($name, $type);
        }

        foreach ($foreignKeys as $column => $definition) {

            $lines[] = sprintf('%s INTEGER %s', $column, $definition);
        }

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
            $table,
            implode(', ', $lines)
        );

        return $sql;
    }


    public function columnToSql($name, $type) {

        switch ($type) {

            case 'primaryKey':
                return $name.' INTEGER PRIMARY KEY';

            case 'boolean':
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
            $table,
            $column,
            $on
        );
    }


    public function addConstraint($table, $name, $constraint) {

        return '';
    }


    public function addIndex($name, $table, $column) {

        return sprintf(
            'CREATE INDEX IF NOT EXISTS `%s` ON `%s`(`%s`)',
            $name,
            $table,
            $column
        );
    }
    
    
    public function addUnique($name, $table, $columns) {

        if (is_array($columns)) {

            $columns = implode(', ', $columns);
        }

        return sprintf(
            'CREATE UNIQUE INDEX IF NOT EXISTS `%s` ON `%s`(%s)',
            $name,
            $table,
            $columns
        );
    }
}


class PostgresBuilder extends SqlBuilder {


    public function createTable($table, array $columns, array $foreignKeys = array()) {

        $lines = array();

        foreach ($columns as $name => $type) {

            $lines[] = $this->columnToSql($name, $type);
        }

        foreach ($foreignKeys as $column => $definition) {

            $lines[] = sprintf('%s INTEGER %s', $column, $definition);
        }

        $sql = sprintf('CREATE TABLE %s (%s)',
            $table,
            implode(', ', $lines)
        );

        return $sql;
    }


    public function columnToSql($name, $type) {

        switch ($type) {

            case 'primaryKey':
                return $name.' SERIAL, PRIMARY KEY('.$name.')';

            case 'integer':
                return $name.' INTEGER';

            case 'boolean':
                return $name.' BOOLEAN';

            case 'numeric':
                return $name.' DECIMAL(10,2)';

            case 'float':
                return $name.' FLOAT';

            case 'binary':
                return $name.' BYTEA';

            case 'string':
                return $name.' VARCHAR(255)';
            
            case 'date':
                return $name.' DATE';

            case 'datetime':
                return $name.' TIMESTAMP';

            case 'time':
                return $name.' TIME';

            case 'text':
                return $name.' TEXT';

            default:
                return $name.' TEXT';
        }
    }


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
            $table,
            $column,
            $on
        );
    }


    public function addConstraint($table, $name, $constraint) {

        return '';
    }


    public function addIndex($name, $table, $column) {

        return sprintf(
            'CREATE INDEX `%s` ON `%s`(`%s`)',
            $name,
            $table,
            $column
        );
    }
    
    
    public function addUnique($name, $table, $columns) {

        if (is_array($columns)) {

            $columns = implode(', ', $columns);
        }

        return sprintf(
            'CREATE UNIQUE INDEX `%s` ON `%s`(%s)',
            $name,
            $table,
            $columns
        );
    }

}


class MysqlBuilder extends SqlBuilder {


    public function createTable($table, array $columns, array $foreignKeys = array(), array $indexes = array()) {

        $lines = array();

        foreach ($columns as $name => $type) {

            $lines[] = $this->columnToSql($name, $type);
        }

        foreach ($foreignKeys as $column => $definition) {

            $lines[] = sprintf('%s INT(10)', $column);
            $lines[] = sprintf('INDEX %s (%s)', $column.'_idx', $column);
            $lines[] = sprintf('FOREIGN KEY (%s) %s', $column, $definition);
        }

        foreach ($indexes as $column) {

            $lines[] = sprintf('INDEX %s (%s)', $column.'_idx', $column);
        }

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)',
            $table,
            implode(', ', $lines)
        );

        $sql .= ' ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        return $sql;
    }


    public function columnToSql($name, $type) {

        switch ($type) {

            case 'primaryKey':
                return $name.' INT(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY('.$name.')';

            case 'integer':
                return $name.' INT';

            case 'boolean':
                return $name.' TINYINT(1)';

            case 'numeric':
                return $name.' DECIMAL(10,2)';

            case 'float':
                return $name.' FLOAT';

            case 'binary':
                return $name.' BLOB';

            case 'string':
                return $name.' VARCHAR(255)';
            
            case 'date':
                return $name.' DATE';

            case 'datetime':
                return $name.' DATETIME';

            case 'time':
                return $name.' TIME';

            case 'text':
                return $name.' LONGTEXT';

            default:
                return $name.' TEXT';
        }
    }


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
            $table,
            $column,
            $on
        );
    }


    public function addConstraint($table, $name, $constraint) {

        return '';
    }


    public function addIndex($name, $table, $column) {

        return sprintf(
            'CREATE INDEX `%s` ON `%s`(`%s`)',
            $name,
            $table,
            $column
        );
    }
    
    
    public function addUnique($name, $table, $columns) {

        if (is_array($columns)) {

            $columns = implode(', ', $columns);
        }

        return sprintf(
            'CREATE UNIQUE INDEX `%s` ON `%s`(%s)',
            $name,
            $table,
            $columns
        );
    }
}
