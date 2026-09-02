# Gatovel Database

Database module for the Gatovel Framework.

## Overview

Gatovel Database is an independent database module designed to provide database functionality without coupling it directly to the Gatovel Framework core.

It provides a simple and extensible API for working with databases, including connections, queries, transactions, migrations, and schema management.

## Features

* Database connections
* Query Builder
* Transactions
* Migrations
* Migration rollback
* Schema management
* Database grammars

  * MySQL
  * PostgreSQL
  * SQLite
* PDO-based database access
* Framework-independent architecture

## Requirements

* PHP 8.3+
* PDO
* PDO driver for the database being used

## Installation

Install the package using Composer:

```bash
composer require gatovel/database
```

## Basic Usage

Connect to a database:

```php
use Gatovel\Database\Database;

Database::connect([
    'connection' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'example',
    'username' => 'root',
    'password' => 'password',
]);
```

### Query Builder

```php
$users = Database::table('users')
    ->get();
```

### Transactions

```php
$transaction = Database::transaction();

$transaction->begin();

try {
    // Database operations

    $transaction->commit();
} catch (\Throwable $e) {
    $transaction->rollback();

    throw $e;
}
```

### Schema

```php
$schema = Database::schema();
```

### Migrations

Migrations can be executed through the `MigrationRunner`:

```php
use Gatovel\Database\migration\MigrationRunner;

$runner = new MigrationRunner();

$runner->migrate(__DIR__ . '/migration');
```

Rollback the last migration batch:

```php
$runner->rollbackLastBatch(
    __DIR__ . '/migration'
);
```

## Architecture

The module is intentionally separated from the Gatovel Framework core.

```text
Gatovel Framework
       │
       ├── Gatovel CLI
       │
       └── Gatovel Database
               │
               ├── Connection
               ├── Query Builder
               ├── Transactions
               ├── Migrations
               ├── Schema
               └── Grammars
```

This architecture allows the database module to evolve independently and potentially be reused by other applications or projects.

## License

This project is licensed under the MIT License.

