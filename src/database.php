<?php

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
}

