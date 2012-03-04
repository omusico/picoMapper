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
 * Schema handler
 *
 * @author Frédéric Guillot
 */
class Schema {

    /**
     * Migration directory
     *
     * @access private
     * @static
     * @var string
     */
    private static $migrationDirectory = 'migrations';


    /**
     * Database instance
     *
     * @access private
     * @var \picoMapper\Database
     */
    private $db;


    /**
     * Builder instance
     *
     * @access private
     * @var \picoMapper\Builder
     */
    private $builder;


    /**
     * Migration files
     *
     * @access private
     * @var array
     */
    private $migrationFiles = array();


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {

        $this->db = Database::getInstance();
        $this->builder = BuilderFactory::getInstance();
    }


    /**
     * Create version table
     *
     * @access public
     */
    public function createVersionTable() {

        $sql = $this->builder->addTable(
            'schema_version',
            array('version' => 'string')
        );
        
        try {

            $this->db->beginTransaction();
            $this->db->exec($sql);
            $this->db->commit();
        }
        catch (\PDOException $e) {

            $this->db->rollback();

            throw new DatabaseException('Unable to create the version table');
        }   
    }


    /**
     * Get last version from the database
     *
     * @access public
     * @return string Schema version
     */
    public function getLastVersionFromDatabase() {

        $rq = $this->db->prepare('SELECT version FROM schema_version');
        $rq->execute();
        $rs = $rq->fetchAll();

        if (isset($rs[0])) return $rs[0]['version'];
        return '';
    }


    /**
     * Get last schema version from migrations
     *
     * @access public
     * @return string Schema version
     */
    public function getLastVersionFromDirectory() {

        if (is_dir(self::$migrationDirectory)) {

            $dir = new \DirectoryIterator(self::$migrationDirectory);
            $this->migrationFiles = array();
            
            foreach ($dir as $fileinfo) {
                
                if (! $fileinfo->isDot() && substr($fileinfo->getFilename(), -4) == '.php') {
                    
                    $this->migrationFiles[] = substr($fileinfo->getFilename(), 0, -4);
                }
            }

            if (! empty($this->migrationFiles)) {

                rsort($this->migrationFiles);
                return $this->migrationFiles[0];
            }
        }

        return '';
    }


    /**
     * Execute a migration
     *
     * @access public
     * @param string $version Version to execute
     */
    public function processMigration($version) {

        $filename = self::$migrationDirectory.DIRECTORY_SEPARATOR.$version.'.php';

        if (file_exists($filename)) {

            require_once $filename;

            $className = 'Version'.$version;
            $m = new $className();
            $m->up();
            $m->execute();

            return true;
        }

        return false;
    }


    /**
     * Compare the database version and the last migration version
     *
     * @access public
     */
    public function compareVersion() {

        $last_version = $this->getLastVersionFromDirectory();

        if ($last_version === '') {

            throw new \RuntimeException('Unable to find a migration file');
        }

        $current_version = $this->getLastVersionFromDatabase();

        if ($current_version === '' || ($current_version < $last_version)) {
           
            $filesToProcess = $this->migrationFiles;
            sort($filesToProcess);

            foreach ($filesToProcess as $file) {

                if ($file > $current_version) {

                    $this->processMigration($file);
                }
            }

            if ($current_version === '') {

                $rq = $this->db->prepare('INSERT INTO schema_version (version) VALUES (?)');
            }
            else {             

                $rq = $this->db->prepare('UPDATE schema_version SET version=?');
            }

            $rq->execute(array($last_version));
        }
    }


    /**
     * Change the default migration directory
     *
     * @access public
     * @param string $migrationDirectory Migration directory
     */
    public static function config($migrationDirectory) {

        self::$migrationDirectory = $migrationDirectory;
    }


    /**
     * Update the database schema
     *
     * @access public
     * @static
     */
    public static function update() {

        $schema = new Schema();
        $schema->createVersionTable();
        $schema->compareVersion();
    }
}

