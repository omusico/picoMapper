<?php

namespace picoMapper;


class DatabaseException extends \Exception {}


class Database {

    private static $dsn = null;
    private static $user = null;
    private static $password = null;
    private static $pdo = null;


    public static function config($dsn, $user = null, $password = null) {

        self::$dsn = $dsn;
        self::$user = $user;
        self::$password = $password;
    }


    public static function getDriver() {

        if (! self::$dsn) return '';

        list($driver,) = explode(':', self::$dsn);
        
        return $driver;
    }


    public static function getInstance() {

        if (self::$pdo === null) {

            self::$pdo = new \PDO(self::$dsn, self::$user, self::$password);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }


    public static function closeInstance() {

        self::$pdo = null;
    }
}

