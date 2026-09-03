# Migrations

Migrations provide a simple way to create and manage database structure changes using PHP classes.

A migration defines two operations:

* `up()` — applies the database change.
* `down()` — reverses the database change.

## Migration Structure

A migration extends the `Migration` class:

```php
<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\migration\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        // Create table
    }

    public function down(): void
    {
        // Drop table
    }
}
```

## Migration Location

Application migrations should be stored in:

```text
src/app/database/migration/
```

Example:

```text
src/
└── app/
    └── database/
        └── migration/
            ├── CreateUsersTable.php
            └── CreateProductsTable.php
```

## Creating a Migration

When using the Gatovel CLI:

```bash
php gatovel make:migration CreateUsersTable
```

The command creates:

```text
src/app/database/migration/CreateUsersTable.php
```

The generated migration contains:

```php
public function up(): void
{
    //
}

public function down(): void
{
    //
}
```

## Running Migrations

Run pending migrations with:

```bash
php gatovel migrate
```

Gatovel Database keeps track of executed migrations in a `migrations` table.

A migration that has already been executed will not be executed again.

## Migration Batches

Migrations are executed in batches.

For example:

```text
Batch 1
├── CreateUsersTable
└── CreateProductsTable

Batch 2
├── AddEmailToUsers
└── CreateOrdersTable
```

This allows the last batch to be rolled back together.

## Rolling Back

To roll back the last migration batch:

```bash
php gatovel migrate:rollback
```

The `down()` method of the migrations in the last batch is executed.

## Schema

Gatovel Database provides the `Schema` class for database structure operations.

Access it through:

```php
use Gatovel\Database\Database;

$schema = Database::schema();
```

The schema layer is designed to provide database structure operations while keeping application migrations separate from the database infrastructure.

## Migration Example

A migration can use the Schema layer to define database structure.

```php
<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\migration\Migration;
use Gatovel\Database\Database;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $schema = Database::schema();

        // Define table structure
    }

    public function down(): void
    {
        $schema = Database::schema();

        // Reverse table structure
    }
}
```

The exact schema operations depend on the methods provided by the current `Schema` implementation.

## Migration Lifecycle

```text
Create Migration
       ↓
php gatovel migrate
       ↓
MigrationLoader
       ↓
MigrationRunner
       ↓
up()
       ↓
migrations table
```

Rollback:

```text
php gatovel migrate:rollback
       ↓
MigrationRunner
       ↓
Last Batch
       ↓
down()
       ↓
Remove Migration Record
```

## Migrations vs Models

Migrations and Models have different responsibilities.

```text
Migration
    ↓
Database structure

Model
    ↓
Application data
```

For example:

```text
CreateUsersTable
    → creates the users table

User
    → represents a user in the application
```

Migrations should therefore remain in:

```text
src/app/database/migration/
```

while application models belong in:

```text
src/app/models/
```

## Next Step

To populate the database with application data, see:

[Seeders →](seeders.md)
