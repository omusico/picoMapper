<?php

// A collaborative task manager
// The main idea is to assign a task to many users...


require '../../src/picoMapper.php';


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
     * @type text
     */
    public $content;

    /**
     * @type datetime
     */
    public $created_at;

    /**
     * @hasMany People
     */
    public $people;
}

/**
 * @table people
 */
class People extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type integer
     */
    public $progression;

    /**
     * @belongsTo User
     */
    public $user;

    /**
     * @belongsTo Task
     */
    public $task;

    /**
     * @foreignKey User
     */
    public $user_id;

    /**
     * @foreignKey Task
     */
    public $task_id;
}


function displayTask(&$t) {

    echo PHP_EOL.$t->content.PHP_EOL;

    foreach ($t->people as $person) {

        echo $person->user->name.' => '.$person->progression.' %'.PHP_EOL;
    }
}


\picoMapper\Database::config('sqlite::memory:');

$u1 = new User();
$u1->name = 'Toto';
$u1->save();

$u2 = new User();
$u2->name = 'Titi';
$u2->save();

$t1 = new Task();
$t1->content = 'Go to bed!';
$t1->created_at = date('c');
$t1->people[] = new People(array('user' => $u1, 'progression' => 0));
$t1->people[] = new People(array('user' => $u2, 'progression' => 0));
$t1->saveAll();

displayTask($t1);

$t1->people[0]->progression = 60;
$t1->people[1]->progression = 10;
$t1->saveAll();

displayTask(Task::findById(1));

$people = People::find()
    ->where('User.name = ? AND Task.id = ?', 'Titi', 1)
    ->join('User')
    ->join('Task')
    ->fetchOne();

$people->progression = 80;
$people->save();

displayTask(Task::findById(1));

$t1 = new Task();
$t1->content = 'Wake up!';
$t1->created_at = date('c');
$t1->people[] = new People(array('user' => $u2, 'progression' => 50));
$t1->saveAll();


echo PHP_EOL.'Task for Titi'.PHP_EOL;

$tasks = Task::find()
    ->where('People.user_id = ?', $u2->id)
    ->join('People')
    ->fetchAll();

foreach ($tasks as $t) {

    displayTask($t);
}

