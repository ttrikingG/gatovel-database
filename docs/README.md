# Gatovel Database

Database module for Gatovel Framework.

Gatovel Database provides a simple and modular database layer with support for connections, Query Builder, transactions, migrations, seeders and Active Record.

## Documentation

| Component                         | Description                               |
| --------------------------------- | ----------------------------------------- |
| [Installation](installation.md)   | Install and configure Gatovel Database    |
| [Configuration](configuration.md) | Configure database connections            |
| [Query Builder](query-builder.md) | Build and execute database queries        |
| [Transactions](transactions.md)   | Work with database transactions           |
| [Migrations](migrations.md)       | Create and manage database structure      |
| [Seeders](seeders.md)             | Populate the database with initial data   |
| [Active Record](active-record.md) | Work with database records through models |

## Architecture

```text
Application
     │
     ├── Models
     │
     ▼
Active Record
     │
     ▼
Query Builder
     │
     ▼
Grammar
     │
     ▼
Connection
     │
     ▼
PDO
     │
     ▼
Database
```

## Supported Databases

* MySQL
* PostgreSQL
* SQLite

## Requirements

* PHP 8.3 or higher
* PDO
* PDO driver for the database being used

## Installation

```bash
composer require gatovel/database
```

For detailed installation instructions, see:

[Installation →](installation.md)

## License

Gatovel Database is open-source software licensed under the [MIT License](../LICENSE).
