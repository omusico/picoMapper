Model metadata
==============

For the configuration picoMapper use annotations. 
A model class must extends from `\picoMapper\Model`. 
Each property must be public and the default value at null. 


Supported annotations
---------------------

Annotations must be specified inside a Docblock comment.

At the class level:

- `@table mytable`: Set the table name used by the model

For properties:

- `@type type`: Column type, __each column must have a specified type__
- `@rule rulename parameter1 parametern`: Validator rule and parameters
- `@foreignKey ModelName`: The property is a foreign key for the specified model
- `@belongsTo ModelName`: The property is a "belongs to" relation with the specified model
- `@hasMany ModelName`: The property is a "has many" relation with the specified model
- `@hasOne ModelName`: The property is a "has one" relation with the specified model


Supported column types
----------------------

- `primaryKey`: Each model __must have a defined primary key__, for all drivers this column is an integer auto increment
- `integer`
- `date`
- `datetime`
- `time`
- `boolean`
- `decimal`
- `numeric`
- `real`
- `float`
- `string`
- `text`
- `binary`

Supported validator rules

- `required`: The field can't be empty
- `unique`: The field must be unique
- `datetime format`: The specified date format must be correct and the date valid
- `email`: Check email address
- `greaterThan value`: The field must be greater than the specified value
- `lessThan value`: The field must be less than the specified value
- `greaterThanOrEqual value`: The field must be greater than or equal to the specified value
- `lessThanOrEqual value`: The field must be less than or equal to the specified value
- `maxLength value`: The field must be shorter than the specified value
- `minLength value`: The field must be longer than the specified value
- `numeric`: The field must be numeric
- `postCode`: Validate a french post code

Example
-------

    /**
     * @table users
     */
    class User extends \picoMapper\Model {

        /**
         * @type primaryKey
         */
        public $id;

        /**
         * @type string
         * @rule required
         * @rule unique
         */
        public $name;

        /**
         * @hasMany Task
         */
        public $tasks;
    }


    /**
     * @table tasks
     */
    class Task extends \picoMapper\Model {

        /**
         * @type primaryKey
         */
        public $id;

        /**
         * @type string
         * @rule required
         * @rule maxLength 100
         */
        public $name;

        /**
         * @type integer
         * @rule numeric
         * @rule >= 10
         */
        public $something;

        /**
         * @belongsTo User
         */
        public $user;

        /**
         * @foreignKey User
         */
        public $user_id;
    }

