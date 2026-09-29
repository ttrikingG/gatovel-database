# Seeders

Seeders provide a simple way to populate the database with application data.

They are useful for:

- Initial application data
- Development data
- Test data
- Default records

## Seeder Structure

A seeder extends the `Seeder` class and implements the `run()` method:

```php
<?php

namespace app\database\seeder;

use Gatovel\Database\Database;
use Gatovel\Database\seeder\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Database::table('users')->insert([
            'name' => 'Tom Garcia',
            'email' => 'tom@example.com',
        ]);
    }
}
```

The base Seeder contract is intentionally simple:

```php
abstract class Seeder
{
    abstract public function run(): void;
}
```

The database package does not impose application-specific seeder behavior.

## Seeder Location

Application seeders should be stored in:

```text
src/app/database/seeder/
```

Example:

```text
src/
└── app/
    └── database/
        └── seeder/
            ├── UserSeeder.php
            └── ProductSeeder.php
```

## Creating a Seeder

When using Gatovel CLI:

```bash
php gatovel make:seeder UserSeeder
```

The generated seeder should follow the Seeder contract:

```php
<?php

namespace app\database\seeder;

use Gatovel\Database\seeder\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}
```

The CLI package is responsible for creating the application seeder file.

## Adding Data

Use the Query Builder inside `run()`:

```php
public function run(): void
{
    Database::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
}
```

Multiple records can be inserted by calling `insert()` multiple times:

```php
public function run(): void
{
    Database::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    Database::table('users')->insert([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
}
```

A seeder can use the normal database API, including the Query Builder and other services provided by `Database`.

## SeederRunner

The database package provides the generic `SeederRunner`:

```php
use Gatovel\Database\seeder\SeederRunner;

$runner = new SeederRunner();

$runner->run([
    new UserSeeder(),
    new ProductSeeder(),
]);
```

Seeders are executed in the order in which they are supplied.

Conceptually:

```text
SeederRunner
    ↓
UserSeeder::run()
    ↓
ProductSeeder::run()
```

`SeederRunner` is responsible only for executing the supplied Seeder instances.

It does not discover application files, manage batches or track which seeders have previously run.

## Running Seeders

When integrated with Gatovel CLI, application seeders can be executed with:

```bash
php gatovel db:seed
```

Application-level discovery and loading of seeder files are responsibilities of the CLI/application integration.

At the database-package level, `SeederRunner` receives the Seeder objects that should be executed.

## Seeder Flow

```text
Application / CLI
       ↓
Load Seeder objects
       ↓
SeederRunner
       ↓
Seeder::run()
       ↓
Query Builder
       ↓
Database
```

This keeps the database package independent from the application directory structure and CLI implementation.

## Seeders and Migrations

Migrations and seeders have different responsibilities:

```text
Migration
    ↓
Database structure

Seeder
    ↓
Database data
```

A common workflow is:

```text
Migration
    ↓
Create tables
    ↓
Seeder
    ↓
Insert initial data
```

For example:

```text
20260929_120000_create_users_table
        ↓
    users table
        ↓
    UserSeeder
        ↓
    user records
```

Unlike migrations, seeders are not registered in the `migrations` table and do not have batches or rollback operations.

## Repeated Execution

Seeders execute whenever they are supplied to `SeederRunner`.

For example:

```php
$runner->run([
    new UserSeeder(),
]);
```

Running the same seeder again executes `run()` again.

Therefore, repeated execution may create duplicate records unless the application implements its own checks, unique database constraints or idempotent seeding logic.

## Responsibility

The separation of responsibilities is:

```text
gatovel/database
    ↓
Seeder contract
SeederRunner

Application
    ↓
Concrete Seeder classes
Seeder data

gatovel/cli
    ↓
Commands and application integration
```

This keeps `gatovel/database` reusable without requiring Gatovel CLI or the main framework.

## Next Step

To work with database records through application models, see:

[Active Record →](active-record.md)
