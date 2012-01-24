<?php

namespace picoMapper;


/**
 * Metadata storage
 *
 * @author Frédéric Guillot
 */
class MetadataStorage {

    /**
     * Container
     *
     * @access private
     * @static
     * @var array
     */
    private static $store = array();


    /**
     * Registered annotations for the parser
     *
     * @access private
     * @static
     * @var array
     */
    private static $annotations = array(
        'table',
        'type',
        'rule',
        'foreignKey',
        'hasMany',
        'hasOne',
        'belongsTo'
    );


    /**
     * Get a metadata instance for the specified model
     *
     * @access public
     * @param string $model Class name
     * @return \picoMapper\Metadata
     */
    public static function get($model) {

        if (! isset(self::$store[$model])) {

            $parser = new MetadataParser($model);
            $parser->registerAnnotations(self::$annotations);

            self::$store[$model] = new Metadata($parser->execute());
        }

        return self::$store[$model];
    }
}

