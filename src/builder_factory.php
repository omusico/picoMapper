<?php

namespace picoMapper;


/**
 * Builder factory
 */
class BuilderFactory {


    /**
     * Get a builder instance according to the driver
     *
     * @param string $driver Database driver name
     * @return mixed SQL Builder instance
     */
    public static function getInstance($driver = '') {

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

