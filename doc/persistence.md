Persistance
===========

Save a model
============

    $user = new User();
    $user->name = 'Toto';
    $user->save();

- The `save()` method performs an insert or an update to the database
- An insert is done when the primary key value is empty
- By default, the model is validated before saving
- To skip validation: `$user->save(false);`
- If there is a validator error, a `ValidatorException` is thrown
- All validators errors are available with the method `$user->getErrors()`
- If there is a database error, a `DatabaseException` is thrown
- Foreign keys values of belongsTo relations are automatically fetched

Save a model and all relations
==============================

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

