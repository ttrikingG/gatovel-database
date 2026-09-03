# Query Builder

The Query Builder provides a simple and expressive way to interact with the database without writing raw SQL.

## Basic Usage

```php
use Gatovel\Database\Database;

$users = Database::table('users')
    ->select()
    ->get();
```

Generated SQL:

```sql
SELECT * FROM users;
```

---

## Selecting Columns

```php
$users = Database::table('users')
    ->select(['id', 'name', 'email'])
    ->get();
```

Generated SQL:

```sql
SELECT id, name, email FROM users;
```

---

## Where Conditions

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->get();
```

Generated SQL:

```sql
SELECT * FROM users WHERE name = ?;
```

Custom operators:

```php
$users = Database::table('users')
    ->where('id', 10, '>')
    ->get();
```

Generated SQL:

```sql
SELECT * FROM users WHERE id > ?;
```

---

## Multiple Conditions

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->where('email', 'tom@example.com')
    ->get();
```

Generated SQL:

```sql
SELECT * FROM users
WHERE name = ?
AND email = ?;
```

---

## First Result

```php
$user = Database::table('users')
    ->where('id', 1)
    ->first();
```

Returns:

```php
[
    'id' => 1,
    'name' => 'Tom',
    'email' => 'tom@example.com',
]
```

If no record is found:

```php
null
```

---

## Limit

```php
$users = Database::table('users')
    ->limit(5)
    ->get();
```

Generated SQL:

```sql
SELECT * FROM users LIMIT 5;
```

---

## Insert

```php
Database::table('users')->insert([
    'name' => 'Tom',
    'email' => 'tom@example.com',
]);
```

Generated SQL:

```sql
INSERT INTO users (name, email)
VALUES (?, ?);
```

Returns:

```php
true
```

or

```php
false
```

---

## Update

```php
Database::table('users')
    ->where('id', 1)
    ->update([
        'name' => 'Tom Garcia'
    ]);
```

Generated SQL:

```sql
UPDATE users
SET name = ?
WHERE id = ?;
```

---

## Delete

```php
Database::table('users')
    ->where('id', 1)
    ->delete();
```

Generated SQL:

```sql
DELETE FROM users
WHERE id = ?;
```

---

## Example

```php
$users = Database::table('users')
    ->select(['id', 'name'])
    ->where('id', 5, '>')
    ->limit(10)
    ->get();
```

---

## Under the Hood

```text
Application
     ↓
QueryBuilder
     ↓
Grammar
     ↓
PDO
     ↓
Database
```

The Query Builder uses database-specific grammars internally:

```text
mysql  → MySQLGrammar
pgsql  → PostgresGrammar
sqlite → SQLiteGrammar
```

This allows the same API to work with multiple database systems.

## Next Step

To execute multiple operations safely, see:

[Transactions →](transactions.md)
