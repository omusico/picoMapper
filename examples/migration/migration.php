<?php

require '../../src/picoMapper.php';

\picoMapper\Database::config('mysql:host=localhost;dbname=picomapper', 'root', '');
//\picoMapper\Database::config('sqlite::memory:');

\picoMapper\Schema::update();


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
     */
    public $name;

    /**
     * @belongsTo User
     */
    public $user;

    /**
     * @foreignKey User
     */
    public $user_id;
}


$u = new User();
$u->name = 'toto';
$u->save();

$t = new Task();
$t->name = 'Task #1';
$t->user = $u;
$t->save();

$t = new Task();
$t->name = 'Task #2';
$t->user = $u;
$t->save();

$tasks = Task::find()
    ->where('Task.user_id = ?', $u->id)
    ->fetchAll();

foreach ($tasks as $task) {

    var_dump($task->name);
}

