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
 * Database exception
 *
 * @author Frédéric Guillot
 */
class DatabaseException extends \Exception {}


/**
 * Database
 *
 * @author Frédéric Guillot
 */
class Database {

    /**
     * PDO DSN
     *
     * @access private
     * @static
     * @var string
     */
    private static $dsn = null;


    /**
     * Database username
     *
     * @access private
     * @static
     * @var string
     */
    private static $user = null;


    /**
     * Databse password
     *
     * @access private
     * @static
     * @var string
     */
    private static $password = null;


    /**
     * PDO instance
     *
     * @access private
     * @static
     * @var PDO
     */
    private static $pdo = null;


    /**
     * Setup database configuration
     *
     * @access private
     * @static
     * @param string $dsn PDO DSN
     * @param string $user Database username
     * @param string $password Database password
     */
    public static function config($dsn, $user = null, $password = null) {

        self::$dsn = $dsn;
        self::$user = $user;
        self::$password = $password;
    }


    /**
     * Get the current database driver
     *
     * @access public
     * @static
     * @return string Driver name
     */
    public static function getDriver() {

        if (! self::$dsn) return '';

        list($driver,) = explode(':', self::$dsn);
        
        return $driver;
    }


    /**
     * Get the current database instance
     *
     * @access public
     * @static
     */
    public static function getInstance() {

        if (self::$pdo === null) {

            self::$pdo = new \PDO(self::$dsn, self::$user, self::$password);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }


    /**
     * Close the current database instance
     *
     * @access public
     * @static
     */
    public static function closeInstance() {

        self::$pdo = null;
    }

    
    /**
     * Bind all parameters with the correct type
     *
     * @access public
     * @static
     * @param PDOStatement $statement PDO statement
     * @param array $values List of parameters
     */
    public static function bindValues(\PDOStatement &$statement, array $values) {

        for ($i = 0, $ilen = count($values); $i < $ilen; ++$i) {

            if (is_int($values[$i])) {

                $type = \PDO::PARAM_INT;
            }
            else if (is_bool($values[$i])) {

                $type = \PDO::PARAM_BOOL;
            }
            else if (is_null($values[$i])) {

                $type = \PDO::PARAM_NULL;
            }
            else {

                $type = \PDO::PARAM_STR;
            }

            $statement->bindValue($i + 1, $values[$i], $type);
        }
    }

    
    /**
     * Execute a prepared statement
     *
     * @access public
     * @static
     * @param string $sql SQL request with ? placeholders
     * @param array $values List of parameters
     * @return PDOStatement
     */
    public static function execute($sql, array $values) {

        try {

            $db = self::getInstance();

            $rq = $db->prepare($sql);
            
            self::bindValues($rq, $values);
            
            $rq->execute();

            return $rq;
        }
        catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage());
        }
    }
}

