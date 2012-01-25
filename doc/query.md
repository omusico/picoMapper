Query
=====

Fetch one record with magic methods
-----------------------------------

    User::findById(2);
    
    User::findByName('Toto');

Fetch all record
----------------

    User::findAll();

Count the number of records
---------------------------

This method perform a `COUNT(*)` SQL command.

    User::count();
    
    User::countByName('Toto');

Custom queries
--------------

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

- `fetchAll()` method return a collection of models, if nothing is found the collection is empty
- `fetchOne()` method return a model instance or null if nothing is found
- `count()` return a integer
- `fields()` is to get only some columns, by default all columns of the current model are fetched
- `asc('column')` performs a sort by column in the upward direction
- `desc('column')` same thing but in the opposite direction
- `limit(number)` to got only some record
- `offset(number)` same as in SQL
- `where()` To put a condition, many conditions can be used
- All table join are done automatically


How to navigate through relations
----------------------------------

For a belongsTo or hasOne:

    $task = Task::findById(5);
    
    echo $task->name; // Task #1
    
    echo $task->user->name; // Toto

For a hasMany:

    $user = User::findById(6);
    
    // Iterate through all tasks
    
    foreach ($user->tasks as $task) {

        echo $task->name;
    }
    
    // Fetch only the second task
    
    $user->tasks[1]->name;

    // Get the number of records
    
    $user->tasks->count();

Deep navigation:

    $user->tasks[0]->user->name;


How to handle results
---------------------

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


How to delete one or many records
---------------------------------

    User::Query()->delete('id = ?', 5); // Remove the record with id 5

    User::Query()->delete(
        'group_id = ? AND (name = ? OR status = ?)',
        1, 'toto', 3
    );

