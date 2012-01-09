<?php

namespace picoMapper;


class MetadataStorage {

    private static $store = array();

    private static $annotations = array(
        'table',
        'type',
        'rule',
        'foreignKey',
        'hasMany',
        'hasOne',
        'belongsTo'
    );


    public static function get($model) {

        if (! isset(self::$store[$model])) {
            
            $reflection = new \ReflectionClass('\picoMapper\Model');
            $excludeMethods = array();

            foreach ($reflection->getMethods() as $m) {

                $excludeMethods[] = $m->getName();
            }

            $parser = new MetadataParser($model);
            $parser->excludeMethods($excludeMethods);
            $parser->registerAnnotations(self::$annotations);

            self::$store[$model] = new Metadata($parser->execute());
        }

        return self::$store[$model];
    }
}

