# Transactions

Transactions allow multiple database operations to be executed as a single unit.

If an operation fails, the application can roll back the transaction and restore the previous database state.

## Basic Usage

```php
use Gatovel\Database\Database;

$transaction = Database::transaction();

try {

    $transaction->begin();

    Database::table('users')->insert([
        'name' => 'Tom',
        'email' => 'tom@example.com',
    ]);

    $transaction->commit();

} catch (\Throwable $exception) {

    $transaction->rollback();

    throw $exception;
}
```

## Transaction Flow

```text
Begin
  ↓
Operation 1
  ↓
Operation 2
  ↓
Operation 3
  ↓
Commit
```

If an error occurs:

```text
Begin
  ↓
Operation 1
  ↓
Operation 2
  ↓
Error
  ↓
Rollback
```

## Begin

Start a transaction with:

```php
$transaction->begin();
```

## Commit

When all operations succeed:

```php
$transaction->commit();
```

The changes are permanently applied to the database.

## Rollback

If an operation fails:

```php
$transaction->rollback();
```

The changes made during the transaction are reverted.

## Example

A transaction can be useful when multiple operations depend on each other:

```php
use Gatovel\Database\Database;

$transaction = Database::transaction();

try {

    $transaction->begin();

    Database::table('users')->insert([
        'name' => 'Tom Garcia',
        'email' => 'tom@example.com',
    ]);

    Database::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $transaction->commit();

    echo 'Transaction completed successfully.';

} catch (\Throwable $exception) {

    $transaction->rollback();

    echo 'Transaction failed: ' . $exception->getMessage();
}
```

## Why Use Transactions?

Transactions are useful when several database operations must succeed together.

For example:

```text
Create User
     +
Create Profile
     +
Create Account
     ↓
All succeed → COMMIT

Any operation fails → ROLLBACK
```

This prevents the database from being left in an inconsistent state.

## Architecture

Transactions operate on the same PDO connection used by the database layer:

```text
Transaction
     ↓
PDO Connection
     ↓
Database
```

The Query Builder can be used normally inside a transaction:

```php
$transaction = Database::transaction();

try {

    $transaction->begin();

    Database::table('users')
        ->where('id', 1)
        ->update([
            'name' => 'Tom Garcia',
        ]);

    $transaction->commit();

} catch (\Throwable $exception) {

    $transaction->rollback();

    throw $exception;
}
```

## Next Step

To manage database structure and schema changes, see:

[Migrations →](migrations.md)
