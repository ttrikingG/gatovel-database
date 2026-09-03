# Configuration

Gatovel Database uses the `Database` class to establish and manage the database connection.

## Basic Configuration

```php
use Gatovel\Database\Database;

Database::connect([
    'connection' => 'mysql',
    'host'       => 'localhost',
    'port'       => 3306,
    'database'   => 'my_database',
    'username'   => 'root',
    'password'   => 'password',
    'charset'    => 'utf8mb4',
]);
```

## Configuration Options

| Option       | Description                          |
| ------------ | ------------------------------------ |
| `connection` | Database driver                      |
| `host`       | Database server hostname or IP       |
| `port`       | Database server port                 |
| `database`   | Database name or SQLite file         |
| `username`   | Database username                    |
| `password`   | Database password                    |
| `charset`    | Character set used by the connection |

## MySQL

Example:

```php
Database::connect([
    'connection' => 'mysql',
    'host'       => 'localhost',
    'port'       => 3306,
    'database'   => 'sistema',
    'username'   => 'root',
    'password'   => 'password',
    'charset'    => 'utf8mb4',
]);
```

The MySQL connection uses the following PDO DSN structure:

```text
mysql:host=HOST;port=PORT;dbname=DATABASE;charset=CHARSET
```

## PostgreSQL

Example:

```php
Database::connect([
    'connection' => 'pgsql',
    'host'       => 'localhost',
    'port'       => 5432,
    'database'   => 'my_database',
    'username'   => 'postgres',
    'password'   => 'password',
    'charset'    => 'utf8',
]);
```

The PostgreSQL connection uses:

```text
pgsql:host=HOST;port=PORT;dbname=DATABASE
```

## SQLite

SQLite does not require a server, username, or password.

Example:

```php
Database::connect([
    'connection' => 'sqlite',
    'database'   => __DIR__ . '/database.sqlite',
    'username'   => '',
    'password'   => '',
    'charset'    => 'utf8',
]);
```

The `database` option points to the SQLite database file.

## Using Environment Variables

For applications, database credentials should normally be stored in environment variables instead of being written directly in the source code.

Example `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sistema
DB_USERNAME=root
DB_PASSWORD=password
DB_CHARSET=utf8mb4
```

The application can then build the configuration:

```php
Database::connect([
    'connection' => $_ENV['DB_CONNECTION'],
    'host'       => $_ENV['DB_HOST'],
    'port'       => $_ENV['DB_PORT'],
    'database'   => $_ENV['DB_DATABASE'],
    'username'   => $_ENV['DB_USERNAME'],
    'password'   => $_ENV['DB_PASSWORD'],
    'charset'    => $_ENV['DB_CHARSET'],
]);
```

Gatovel Database itself does not require a specific environment-variable library. The application is responsible for loading environment variables.

## Connection

Once `Database::connect()` has been called, the connection can be accessed through:

```php
$connection = Database::connection();
```

This returns the underlying PDO connection.

Example:

```php
$connection = Database::connection();

$statement = $connection->query('SELECT 1');

$result = $statement->fetchColumn();

echo $result;
```

## Selecting a Database Driver

Gatovel Database automatically selects the appropriate SQL grammar based on the `connection` option:

```text
mysql  → MySQLGrammar
pgsql  → PostgresGrammar
sqlite → SQLiteGrammar
```

This allows the Query Builder to generate database-specific SQL while keeping the application API consistent.

## Connection Errors

If the database connection cannot be established, Gatovel Database throws an exception:

```php
try {

    Database::connect([
        'connection' => 'mysql',
        'host'       => 'localhost',
        'port'       => 3306,
        'database'   => 'my_database',
        'username'   => 'root',
        'password'   => 'password',
        'charset'    => 'utf8mb4',
    ]);

} catch (\Exception $exception) {

    echo $exception->getMessage();
}
```

## Next Step

After configuring the database connection, you can start executing queries with the Query Builder:

[Query Builder →](query-builder.md)
