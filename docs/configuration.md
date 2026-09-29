# Configuration

Gatovel Database uses the `Database` class to establish and manage the database connection.

The package supports:

- MySQL
- PostgreSQL
- SQLite

The database driver must be explicitly defined through the `connection` option.

## Basic Configuration

Example using MySQL:

```php
use Gatovel\Database\Database;

Database::connect([
    'connection' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'my_database',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
]);
```

There is no implicit default driver. The `connection` option is required.

## Configuration by Driver

The required configuration depends on the selected driver.

### MySQL

Required options:

```text
connection
host
port
database
username
password
charset
```

Example:

```php
Database::connect([
    'connection' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'sistema',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
]);
```

The generated PDO DSN follows:

```text
mysql:host=HOST;port=PORT;dbname=DATABASE;charset=CHARSET
```

### PostgreSQL

Required options:

```text
connection
host
port
database
username
password
```

Example:

```php
Database::connect([
    'connection' => 'pgsql',
    'host' => 'localhost',
    'port' => 5432,
    'database' => 'my_database',
    'username' => 'postgres',
    'password' => 'password',
]);
```

The generated PDO DSN follows:

```text
pgsql:host=HOST;port=PORT;dbname=DATABASE
```

### SQLite

SQLite does not require a database server, username, password or charset.

Required options:

```text
connection
database
```

Example using a database file:

```php
Database::connect([
    'connection' => 'sqlite',
    'database' => __DIR__ . '/database.sqlite',
]);
```

The generated PDO DSN follows:

```text
sqlite:DATABASE
```

For an in-memory SQLite database:

```php
Database::connect([
    'connection' => 'sqlite',
    'database' => ':memory:',
]);
```

This is useful for tests and temporary database operations.

## Configuration Validation

Connection configuration is validated before PDO is created.

The `connection` option:

- must exist;
- must be a string;
- cannot be empty.

The supported values are:

```text
mysql
pgsql
sqlite
```

Each driver also validates its required options.

For example, this configuration is invalid:

```php
Database::connect([
    'connection' => 'mysql',
    'host' => 'localhost',
]);
```

because the remaining required MySQL options are missing.

Required configuration values other than `password` cannot be empty.

The password must be a string, but an empty password is allowed.

## Exceptions

Gatovel Database provides database-specific exceptions under:

```text
Gatovel\Database\exceptions
```

The exception hierarchy starts with:

```php
Gatovel\Database\exceptions\DatabaseException
```

Specific connection-related exceptions include:

```text
DatabaseException
├── ConfigurationException
├── ConnectionException
└── UnsupportedDriverException
```

### ConfigurationException

Thrown when required connection configuration is missing or invalid.

Example:

```php
use Gatovel\Database\exceptions\ConfigurationException;

try {
    Database::connect([
        'connection' => 'mysql',
    ]);
} catch (ConfigurationException $exception) {
    echo $exception->getMessage();
}
```

### UnsupportedDriverException

Thrown when an unsupported driver is requested.

Example:

```php
use Gatovel\Database\exceptions\UnsupportedDriverException;

try {
    Database::connect([
        'connection' => 'oracle',
    ]);
} catch (UnsupportedDriverException $exception) {
    echo $exception->getMessage();
}
```

### ConnectionException

Thrown when the configuration is valid but PDO cannot establish the database connection.

Example:

```php
use Gatovel\Database\exceptions\ConnectionException;

try {
    Database::connect([
        'connection' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'my_database',
        'username' => 'root',
        'password' => 'password',
        'charset' => 'utf8mb4',
    ]);
} catch (ConnectionException $exception) {
    echo $exception->getMessage();
}
```

The original PDO exception is preserved as the previous exception.

## PDO Configuration

Connections created by Gatovel Database configure PDO with:

```text
PDO::ATTR_ERRMODE
    → PDO::ERRMODE_EXCEPTION

PDO::ATTR_DEFAULT_FETCH_MODE
    → PDO::FETCH_ASSOC

PDO::ATTR_EMULATE_PREPARES
    → false
```

This means database errors are reported as exceptions, associative arrays are the default fetch format, and native prepared statements are preferred.

## Accessing the Connection

After `Database::connect()` succeeds, the underlying PDO instance is available through:

```php
$connection = Database::connection();
```

Example:

```php
$connection = Database::connection();

$statement = $connection->query('SELECT 1');

$result = $statement->fetchColumn();

echo $result;
```

Calling `Database::connection()` before establishing a connection causes a `DatabaseException`.

## Using Environment Variables

Applications should normally keep database credentials outside source code.

Example `.env` for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sistema
DB_USERNAME=root
DB_PASSWORD=password
DB_CHARSET=utf8mb4
```

The application can build the configuration:

```php
Database::connect([
    'connection' => $_ENV['DB_CONNECTION'],
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => $_ENV['DB_CHARSET'],
]);
```

Gatovel Database does not depend on a specific environment-variable library.

Loading environment variables and building the configuration array are responsibilities of the application or framework using the package.

## Driver Selection

`Database::connect()` configures two driver-specific layers.

The Query Builder grammar:

```text
mysql
  → MySQLGrammar

pgsql
  → PostgresGrammar

sqlite
  → SQLiteGrammar
```

And the Schema grammar:

```text
mysql
  → MySQLSchemaGrammar

pgsql
  → PostgresSchemaGrammar

sqlite
  → SQLiteSchemaGrammar
```

Conceptually:

```text
Database::connect()
        ↓
    connection
        ↓
       PDO
        +
   Query Grammar
        +
   Schema Grammar
```

This allows the same public API to generate database-specific SQL for both data queries and schema operations.

## Database Services

After connecting, `Database` provides access to the main database services:

```php
Database::connection();

Database::table('users');

Database::schema();

Database::transaction();
```

Their responsibilities are:

```text
connection()
    → underlying PDO connection

table()
    → Query Builder

schema()
    → database structure operations

transaction()
    → transaction control
```

## Next Step

After configuring the database connection, queries can be executed with:

[Query Builder →](query-builder.md)

Database structure can be managed with:

[Migrations →](migrations.md)
