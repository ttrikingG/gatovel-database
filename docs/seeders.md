# Seeders

Seeders provide a simple way to populate the database with application data.

They are useful for:

* Initial application data
* Development data
* Test data
* Default records

## Seeder Structure

A seeder is a PHP class containing a `run()` method:

```php
<?php

namespace app\database\seeder;

use Gatovel\Database\Database;

class UserSeeder
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

Use the Gatovel CLI:

```bash
php gatovel make:seeder UserSeeder
```

This creates:

```text
src/app/database/seeder/UserSeeder.php
```

The generated file contains:

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

## Adding Data

Use the Query Builder inside the `run()` method:

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

## Running Seeders

Run the application's seeders with:

```bash
php gatovel db:seed
```

The CLI loads the PHP files from:

```text
src/app/database/seeder/
```

and executes their `run()` methods.

## Seeder Flow

```text
php gatovel db:seed
        ↓
SeedCommand
        ↓
Load Seeders
        ↓
SeederRunner
        ↓
run()
        ↓
Query Builder
        ↓
Database
```

## SeederRunner

The database package provides the generic `SeederRunner`:

```php
use Gatovel\Database\seeder\SeederRunner;

$runner = new SeederRunner();

$runner->run([
    new UserSeeder(),
]);
```

The runner is responsible only for executing the supplied seeders.

The application is responsible for defining its own seeders.

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
CreateUsersTable
        ↓
     users table
        ↓
   UserSeeder
        ↓
    User records
```

## Important

Seeders are intended to insert data when they are executed.

Running the same seeder multiple times may create duplicate records unless the application implements its own checks or constraints.

## Next Step

To work with database records through application models, see:

[Active Record →](active-record.md)
