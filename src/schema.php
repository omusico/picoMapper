<?php

namespace picoMapper;


class Schema {

    private static $migrationDirectory = 'migrations';
    private $db;
    private $builder;
    private $migrationFiles = array();


    public function __construct() {

        $this->db = Database::getInstance();
        $this->builder = BuilderFactory::getInstance();
    }


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


    public function getLastVersionFromDatabase() {

        $rq = $this->db->prepare('SELECT version FROM schema_version');
        $rq->execute();
        $rs = $rq->fetchAll();

        if (isset($rs[0])) return $rs[0]['version'];
        return '';
    }


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


    public function processFile($version) {

        $filename = self::$migrationDirectory.DIRECTORY_SEPARATOR.$version.'.php';

        if (file_exists($filename)) {

            require_once $filename;

            $className = 'Version'.$version;
            $m = new $className();
            $m->up();
            $m->execute();
        }
    }


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

                    $this->processFile($file);
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


    public static function config($migrationDirectory) {

        self::$migrationDirectory = $migrationDirectory;
    }


    public static function update() {

        $schema = new Schema();
        $schema->createVersionTable();
        $schema->compareVersion();
    }
}

