Configuration
=============

To configure the database you just need to set the PDO DSN and user credentials. 
PicoMapper support only these drivers:


Mysql
-----

    \picoMapper\Database::config('mysql:host=localhost;dbname=testdb', 'myuser', 'mypassword');


Postgresql
----------

    \picoMapper\Database::config('pgsql:host=localhost;port=5432;dbname=testdb', 'myuser', 'mypassword');

Sqlite
------

    \picoMapper\Database::config('sqlite:/path/to/db.sqlite');

Or for in memory database:

    \picoMapper\Database::config('sqlite::memory:');

