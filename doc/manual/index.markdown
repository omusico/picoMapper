picoMapper
==========

A simple and fast ORM for PHP >= 5.3

This ORM is designed for rapid application development, it's perfect for
a simple CRUD web application or webservice.


Features
--------

- Very easy to configure, we use annotations for model description
- Easy to use: ActiveRecord pattern, inspired from Ruby on Rails
- Support relations: has one, has many, belongs to
- Lightweight, relations are loaded only if necessary (lazy loading)
- Data validation
- Migrations
- Tested, almost everything have a unit test
- Use PDO and only prepared statement
- No code generation


Requirements
------------

- PHP >= 5.3
- PDO drivers: Sqlite, Mysql, Postgresql
- Mysql >= 5.1 or Sqlite 3.x or Postgresql >= 9.1


Version
-------

Version 0.1


Author
------

Frédéric Guillot: <http://fguillot.fr>.


Source code
-----------

On Github: <https://github.com/fguillot/picoMapper>


License
-------

picoMapper is released under the CeCILL-B free software license agreement.
This is similar to the New-BSD license but compatible with the french law.


Manual
------

- [Database configuration](#configuration)
- [Model metadata](#metadata)
- [Persistence](#persistence)
- [Queries](#queries)
- [Callbacks](#callbacks)
- [Validators](#validators)
- [Migrations](#migrations)


Database configuration {#configuration}
----------------------

To configure the database you just need to set the PDO DSN and user credentials. 
PicoMapper support only these drivers:


### Mysql

    \picoMapper\Database::config('mysql:host=localhost;dbname=testdb', 'myuser', 'mypassword');


### Postgresql


    \picoMapper\Database::config('pgsql:host=localhost;port=5432;dbname=testdb', 'myuser', 'mypassword');

### Sqlite


    \picoMapper\Database::config('sqlite:/path/to/db.sqlite');

Or for in memory database:

    \picoMapper\Database::config('sqlite::memory:');


Model metadata {#metadata}
--------------

For the configuration picoMapper use annotations. 
A model class must extends from `\picoMapper\Model`. 
Each property must be public and the default value at null. 


### Supported annotations

Annotations must be specified inside a Docblock comment.
At the class level:

`@table mytable`
: Set the table name used by the model, by default the table name is same as the class name.

For properties:

`@type type`
: Column type, __each column must have a specified type__

`@rule rulename parameter1 parameterN`
: Validator rule and parameters

`@foreignKey ModelName`
: The property is a foreign key for the specified model

`@belongsTo ModelName`
: The property is a "belongs to" relation with the specified model

`@hasMany ModelName`
: The property is a "has many" relation with the specified model

`@hasOne ModelName`
: The property is a "has one" relation with the specified model


### Supported column types

Each model __must have a defined primary key__, for all drivers this column is an integer auto increment.

- `primaryKey`
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

`required`
: The field can't be empty

`unique`
: The field must be unique

`datetime format`
: The specified date format must be correct and the date valid

`email`
: Check email address

`greaterThan value`
: The field must be greater than the specified value

`lessThan value`
: The field must be less than the specified value

`greaterThanOrEqual value`
: The field must be greater than or equal to the specified value

`lessThanOrEqual value`
: The field must be less than or equal to the specified value

`maxLength value`
: The field must be shorter than the specified value

`minLength value`
: The field must be longer than the specified value

`numeric`
: The field must be numeric

`postCode`
: Validate a french post code

### Example

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


Persistance {#persistence}
-----------

### Save a model data

    $user = new User();
    $user->name = 'Toto';
    $user->save();

- The `save()` method performs an insert or an update to the database
- An insert is done when the primary key value is empty
- After an insert, the primary key value is filled from the database
- By default, the model is validated before saving
- To skip validation: `$user->save(false);`
- If there is a validator error, a `ValidatorException` is thrown
- All validators errors are available with the method `$user->getErrors()`
- If there is a database error, a `DatabaseException` is thrown
- Foreign keys values of belongsTo relations are automatically fetched

### Save a model and all relations

    $user = new User();
    $user->name = 'Toto';
    $user->tasks[] = new Task(array('name' => 'Task #1'));
    $user->saveAll();

or

    $data = array(
        'name' => 'Toto',
        'tasks' => array(
            array('name' => 'Task #1'),
            array('name' => 'Task #2')
        )
    );

    $user = new User($data);
    $user->saveAll();

- All relations are saved inside a transaction, if something go bad there is a rollback
- To skip validation: `$user->saveAll(false);`


Queries {#queries}
-------


### Fetch one record with magic methods

    User::findById(2);
    
    User::findByName('Toto');

### Fetch all record

    User::findAll();

### Count the number of records

This method perform a `COUNT(*)` SQL command.

    User::count();
    
    User::countByName('Toto');

### Custom queries

Example:

    $result = User::Query()
        ->where('User.name = ? AND User.id >= ?', 'Toto', 2)
        ->desc('name')
        ->limit(10)
        ->offset(2)
        ->fetchAll();

Another one condition with another related model:

    $result = User::Query()
        ->fields('User.name', 'Task.name')
        ->where('User.id = ?', 5)
        ->where('Task.name LIKE ?', 'test')
        ->fetchOne();

Custom condition with count:

    $result = User::Query()
        ->where('User.created_at >= ?', '2010-04-03')
        ->count();

`fetchAll()`
: This method return a collection of models, if nothing is found the collection is empty

`fetchOne()`
: This method return a model instance or null if nothing is found

`count()`
: Performs a `COUNT(*)` command and return an integer

`fields()`
: Useful to get only some columns, by default all columns of the current model are fetched

`asc('column')`
: Performs a sort by column in the upward direction

`desc('column')`
: Same thing but in the opposite direction

`limit(number, skip)`
: To get only some records, skip is the offset value

`where()`
: To put a condition, many conditions can be used

All table join are done automatically


### How to navigate through relations

For a belongsTo or hasOne:

    $task = Task::findById(5);
    
    echo $task->name; // Task #1
    
    echo $task->user->name; // Toto

For hasMany:

    $user = User::findById(6);
    
    // Iterate through all tasks
    
    foreach ($user->tasks as $task) {

        echo $task->name;
    }
    
    // Fetch only the second task
    
    $user->tasks[1]->name;

    // Get the number of records
    
    $user->tasks->count();

Deep navigation though relations:

    $user->tasks[0]->user->group->name;


### How to handle results

All raw SQL records are translated in native PHP types.
By example, a date format becomes a Datetime instance:

    $result->created_at->format('d/m/Y');

To got an Array representation of your model use the method `toArray()`:

    print_r($result->toArray());
    
    // You got:
    
    array(
        'id' => 1,
        'name' => 'Toto',
        'tasks' => array(
            array(
                'id' => 1,
                'name' => 'Task #1',
                'user_id' => 1
            )
        )
    )

Relations are loaded on-demand to save resource (lazy loading):

    // We fetch only the current model from the database
    
    $user = User::findById(1);
    
    var_dump($user->name);
    
    // This relation is loaded only now from the database
    
    var_dump($user->tasks[0]->name);


### How to delete one or many records

    User::Query()->delete('id = ?', 5); // Remove the record with id 5

    User::Query()->delete(
        'group_id = ? AND (name = ? OR status = ?)',
        1, 'toto', 3
    );


Model callbacks {#callbacks}
---------------

You can define custom callbacks inside your models.

- `public function beforeValidate();`
- `public function afterValidate();`
- `public function beforeSave();`
- `public function afterSave();`


Validators {#validators}
----------


### Customize validators error messages

Customs validators messages can be useful for localization. 
Inside your model, you need to fill the property `$validatorMessages`. 

Example:

    $this->validatorMessage = array(
        'required' => 'Valeur obligatoire',
        'unique' => 'Ce champ doit être unique'
    );


A good idea is to create a common model class for your application:


    class AppModel extends \picoMapper\Model {
    
        // common methods
    }
    
    
    class MyModel extends AppModel {
    
        // ...
    
    }

### Use custom validators

1. Create a directory named `validators` inside your projects
2. Create a new file, example: `phone.php`
3. Define a new class, example: `PhoneValidator`
4. Your class must be inside the namespace `\picoMapper\Validators`
5. Your validator must follow the interface `ValidatorInterface`


Migrations {#migrations}
----------

### What is a migration?

A migration is a class that handle schema manipulation in pure PHP. 

- Each migration class must be called: `VersionXXXX`, where XXX is your schema version number. 
By example, you can use a timestamp or just a number.
- Inside this class you need to have two methods: `up()` and `down()`.
The first one is executed during a update and the other one for a downgrade (not yet implemented).


### Example of migration class

    class Version01 extends \picoMapper\Migration {

        public function up() {

            $this->addTable('table_a', 
                array(
                    'id' => 'primaryKey',
                    'data' => 'string'
                )
            );

            $this->addTable('table_b',
                array(
                    'id' => 'primaryKey',
                    'content' => 'string'
                ),
                array(
                    'a_id' => $this->addForeignKey('table_a', 'id')
                )
            );

            $this->addIndex('table_b', 'a_id');
        }


        public function down() {

        }
    }

### How to execute manually a migration?

    $m = new Version01();
    $m->up();
    $m->execute();

### Update your database automatically

- You can update your schema with this method: `Schema::update()`.
- Inside your database, a table named `schema_version` is used to keep the current schema version. 
- All migrations should be stored under the directory `migrations`

