# Installation

## Requirements

Gatovel Database requires:

* PHP 8.3 or higher
* Composer
* PDO
* PDO driver for the database you want to use

Supported database drivers:

* MySQL
* PostgreSQL
* SQLite

## Install with Composer

Install Gatovel Database using Composer:

```bash
composer require gatovel/database
```

Composer will install the package and configure its autoloader automatically.

## Verify Installation

After installation, verify that the package is available:

```bash
composer show gatovel/database
```

You should see information about the installed `gatovel/database` package.

## Autoload

If you are using Composer's autoloader, include:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

The package uses PSR-4 autoloading:

```text
Gatovel\Database\
    ↓
src/database/
```

## Basic Usage

After installation, you can connect to a database using the `Database` class:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

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

Once connected, the database layer is ready to be used.

For connection configuration, see:

[Configuration →](configuration.md)

## Installing a PDO Driver

The PDO extension and the corresponding database driver must be available in your PHP environment.

### MySQL

```bash
sudo apt install php8.3-mysql
```

### PostgreSQL

```bash
sudo apt install php8.3-pgsql
```

### SQLite

```bash
sudo apt install php8.3-sqlite3
```

If you are using Docker, install the required extensions in your PHP image instead of installing them directly on the host.

## Next Step

After installing Gatovel Database, configure your database connection:

[Configuration →](configuration.md)
