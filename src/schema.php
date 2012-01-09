<?php

namespace picoMapper;


class Schema {

    private static $sqlDirectory = 'sql';
    private $db;
    private $builder;
    private $sqlFiles = array();


    public function __construct() {

        $this->db = Database::getInstance();
        $this->builder = Builder::create();
    }


    public function createVersionTable() {

        $sql = $this->builder->createTable('schema_version', array('version' => 'string'));
        $this->db->exec($sql);
    }


    public function getLastVersionFromDatabase() {

        $rq = $this->db->prepare('SELECT version FROM schema_version');
        $rq->execute();
        $rs = $rq->fetchAll();

        if (isset($rs[0])) return $rs[0]['version'];
        return '';
    }


    public function getLastVersionFromDirectory() {

        if (is_dir(self::$sqlDirectory)) {

            $dir = new \DirectoryIterator(self::$sqlDirectory);
            $this->sqlFiles = array();
            
            foreach ($dir as $fileinfo) {
                
                if (! $fileinfo->isDot() && substr($fileinfo->getFilename(), -4) == '.sql') {
                    
                    $this->sqlFiles[] = substr($fileinfo->getFilename(), 0, -4);
                }
            }

            if (! empty($this->sqlFiles)) {

                rsort($this->sqlFiles);
                return $this->sqlFiles[0];
            }
        }

        return '';
    }


    public function processSqlFile($version) {

        $filename = self::$sqlDirectory.DIRECTORY_SEPARATOR.$version.'.sql';

        if (file_exists($filename)) {

            $this->db->exec(file_get_contents($filename));
        }
    }


    public function compareVersion() {

        $last_version = $this->getLastVersionFromDirectory();

        if ($last_version === '') {

            throw new \RuntimeException('Unable to find a sql file');
        }

        $current_version = $this->getLastVersionFromDatabase();

        if ($current_version === '' || ($current_version < $last_version)) {
           
            $filesToProcess = $this->sqlFiles;
            sort($filesToProcess);

            foreach ($filesToProcess as $file) {

                if ($file > $current_version) {

                    $this->processSqlFile($file);
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


    public static function config($sqlDirectory) {

        self::$sqlDirectory = $sqlDirectory;
    }


    public static function update() {

        $schema = new Schema();
        $schema->createVersionTable();
        $schema->compareVersion();
    }
}

