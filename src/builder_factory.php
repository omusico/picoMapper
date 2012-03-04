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
 * Builder factory
 *
 * @author Frédéric Guillot
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

            case 'pgsql':
                return new PostgresBuilder();

            case 'mysql':
                return new MysqlBuilder();

            default:
                throw new \RuntimeException('Unsupported driver');
        }
    }
}

