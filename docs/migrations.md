# Migrations

Migrations provide a versioned way to create and manage database structure changes.

Each migration defines two operations:

- `up()` — applies the database change.
- `down()` — reverses the database change.

## Migration Structure

A migration file must return an instance of `Migration`.

The recommended format uses an anonymous class:

```php
<?php

use Gatovel\Database\Database;
use Gatovel\Database\migration\Migration;
use Gatovel\Database\migration\schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Database::schema()->create(
            'users',
            function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Database::schema()->drop('users');
    }
};
```

The migration file itself is used as the migration identifier.

For example:

```text
20260929_120000_create_users_table.php
```

is registered in the `migrations` table as:

```text
20260929_120000_create_users_table
```

This means migrations do not depend on namespaces or named PHP classes.

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
            ├── 20260929_120000_create_users_table.php
            └── 20260929_121000_create_products_table.php
```

Migration filenames should be unique and sortable.

Using a timestamp prefix keeps migrations in creation order:

```text
YYYYMMDD_HHMMSS_description.php
```

## Creating a Migration

When using the Gatovel CLI:

```bash
php gatovel make:migration CreateUsersTable
```

The generated migration should follow the migration contract described above and return a `Migration` instance.

The CLI package is responsible for generating the migration filename and template.

## Schema

Gatovel Database provides a database-independent Schema layer.

Access it through:

```php
use Gatovel\Database\Database;

$schema = Database::schema();
```

Schema operations are translated to the SQL dialect of the active database driver.

The currently supported drivers are:

- MySQL
- PostgreSQL
- SQLite

## Creating Tables

Use `Schema::create()` with a `Blueprint`:

```php
use Gatovel\Database\Database;
use Gatovel\Database\migration\schema\Blueprint;

Database::schema()->create(
    'users',
    function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    }
);
```

The callback receives a `Blueprint` representing the table structure.

## Available Column Types

The Blueprint provides the following column types:

```php
$table->id();
$table->string('name');
$table->string('code', 100);
$table->text('description');
$table->integer('age');
$table->boolean('active');
$table->timestamp('published_at');
$table->timestamps();
```

`timestamps()` creates:

```text
created_at
updated_at
```

Columns are `NOT NULL` by default. Supported column types can explicitly be nullable:

```php
$table->string('nickname', 255, true);
$table->text('description', true);
$table->integer('age', true);
$table->boolean('active', true);
$table->timestamp('email_verified_at', true);
```

String, integer, and boolean columns can define default values:

```php
$table->string('status', 50, false, 'active');
$table->integer('score', false, 10);
$table->boolean('enabled', false, true);
```

Timestamp columns can explicitly use the current database timestamp as their default:

```php
$table->timestamp('created_at', false, true);
```

This generates the database-specific equivalent of `DEFAULT CURRENT_TIMESTAMP`.

`timestamps()` creates `created_at` and `updated_at` without automatically assigning default values.

## Primary and Unique Constraints

`id()` creates the standard auto-incrementing primary key for the active database driver:

```php
$table->id();
```

Composite primary keys can be defined with `primary()`:

```php
$table->integer('user_id');
$table->integer('role_id');

$table->primary([
    'user_id',
    'role_id',
]);
```

Unique constraints can be defined for one or more columns:

```php
$table->string('email');

$table->unique([
    'email',
]);
```

Composite unique constraints are also supported:

```php
$table->string('provider', 50);
$table->string('provider_user_id');

$table->unique(
    [
        'provider',
        'provider_user_id',
    ],
    'unique_oauth_provider_user'
);
```

The unique constraint name is optional. Columns referenced by `primary()` or `unique()` must already exist in the Blueprint.

## Adding Columns

Columns can be added to an existing table with `addColumn()`:

```php
use Gatovel\Database\Database;
use Gatovel\Database\migration\schema\Blueprint;

Database::schema()->addColumn(
    'users',
    function (Blueprint $table): void {
        $table->string('email');
        $table->boolean('active');
    }
);
```

Each column is added using the SQL grammar of the active driver.

## Dropping Tables

Use:

```php
Database::schema()->drop('users');
```

## Running Migrations

Pending migrations can be executed by the migration runner.

At the package level:

```php
use Gatovel\Database\migration\MigrationRunner;

$runner = new MigrationRunner();

$runner->migrate(
    'src/app/database/migration'
);
```

When integrated with Gatovel CLI, the equivalent command is:

```bash
php gatovel migrate
```

`MigrationLoader` loads migration files in filename order.

A migration that has already been registered in the `migrations` table is skipped.

## Migration Table

Gatovel Database automatically creates the `migrations` table when `MigrationRunner` is initialized.

The table stores:

```text
id
migration
batch
```

Example:

```text
1 | 20260929_120000_create_users_table    | 1
2 | 20260929_121000_create_products_table | 1
3 | 20260930_090000_create_orders_table   | 2
```

The `migration` value corresponds to the migration filename without the `.php` extension.

## Migration Batches

Every execution of pending migrations creates a new batch number.

For example:

```text
Batch 1
├── 20260929_120000_create_users_table
└── 20260929_121000_create_products_table

Batch 2
└── 20260930_090000_create_orders_table
```

This allows the most recent group of migrations to be rolled back together.

## Rolling Back

At the package level:

```php
use Gatovel\Database\migration\MigrationRunner;

$runner = new MigrationRunner();

$runner->rollbackLastBatch(
    'src/app/database/migration'
);
```

When integrated with Gatovel CLI:

```bash
php gatovel migrate:rollback
```

The runner retrieves the migrations from the latest batch and executes their `down()` methods.

After a successful `down()`, the corresponding migration record is removed.

If a migration recorded in the database cannot be found in the supplied migration directory, rollback fails explicitly instead of silently deleting its record.

## Migration Lifecycle

```text
Migration files
      ↓
MigrationLoader
      ↓
LoadedMigration
      ↓
MigrationRunner
      ↓
Check migrations table
      ↓
up()
      ↓
Register filename + batch
```

Rollback:

```text
Last batch
      ↓
Find migration by filename
      ↓
down()
      ↓
Remove migration record
```

## MigrationLoader

`MigrationLoader` reads all `.php` files from the migration directory and sorts them by filename.

Each file must return a valid `Migration` instance.

A file that does not return a `Migration` causes a database exception instead of being silently ignored.

This contract avoids coupling migrations to:

- application namespaces;
- PHP class names;
- global declared-class discovery.

## LoadedMigration

Internally, each loaded migration is represented by a `LoadedMigration`.

It contains:

```text
name
migration instance
```

For:

```text
20260929_120000_create_users_table.php
```

the name is:

```text
20260929_120000_create_users_table
```

and the migration instance is the object returned by the file.

## Database Driver Independence

Application migrations use the same Schema API regardless of the configured database.

For example:

```php
$table->id();
$table->string('name');
$table->boolean('active');
```

The generated SQL differs by driver.

Conceptually:

```text
Blueprint
    ↓
SchemaGrammar
    ├── MySQLSchemaGrammar
    ├── PostgresSchemaGrammar
    └── SQLiteSchemaGrammar
    ↓
Database-specific SQL
```

This keeps application migrations independent from database-specific SQL for the operations supported by the Schema layer.

## Migration Safety

Migration execution follows an important rule:

```text
up() succeeds
    ↓
migration is registered
```

If `up()` fails, the migration is not registered as successfully executed.

Rollback follows:

```text
down() succeeds
    ↓
migration record is removed
```

If `down()` fails, its migration record is preserved.

DDL migrations are not automatically wrapped in transactions by `MigrationRunner`, because transactional behavior for schema changes differs between database engines.

## Migrations vs Models

Migrations and models have different responsibilities:

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
20260929_120000_create_users_table
    → creates the users table

User
    → represents user data in the application
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