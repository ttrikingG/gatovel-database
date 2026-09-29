# Query Builder

The Query Builder provides a simple and expressive way to interact with the database without writing raw SQL.

It uses prepared statements for values and database-specific grammars for SQL generation.

## Basic Usage

```php
use Gatovel\Database\Database;

$users = Database::table('users')
    ->select()
    ->get();
```

Conceptually:

```sql
SELECT * FROM users;
```

`get()` returns an array containing all matching records.

## Selecting Columns

```php
$users = Database::table('users')
    ->select([
        'id',
        'name',
        'email',
    ])
    ->get();
```

Conceptually:

```sql
SELECT id, name, email FROM users;
```

Calling `select()` without arguments selects all columns:

```php
Database::table('users')
    ->select()
    ->get();
```

An empty column list is not allowed:

```php
Database::table('users')
    ->select([]);
```

This causes a `DatabaseException`.

## Where Conditions

Use `where()` to add an `AND` condition:

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
WHERE name = ?;
```

Values are passed separately to PDO as prepared-statement bindings.

## Custom Operators

The third argument of `where()` defines the comparison operator:

```php
$users = Database::table('users')
    ->where('id', 10, '>')
    ->get();
```

Supported operators are:

```text
=
!=
<>
>
>=
<
<=
LIKE
NOT LIKE
```

Operators outside this list cause a `DatabaseException`.

For example:

```php
Database::table('users')
    ->where('id', 1, 'INVALID');
```

is rejected before the query is executed.

## Multiple AND Conditions

Multiple calls to `where()` are joined with `AND`:

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->where('email', 'tom@example.com')
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
WHERE name = ?
AND email = ?;
```

## OR Conditions

Use `orWhere()` when the next condition should use `OR`:

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->orWhere('name', 'Maria')
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
WHERE name = ?
OR name = ?;
```

`where()` and `orWhere()` can be combined:

```php
$users = Database::table('users')
    ->where('active', 1)
    ->orWhere('name', 'Tom')
    ->get();
```

Conditions are compiled in the order they are added.

## LIKE Conditions

For common `LIKE` queries, use `whereLike()`:

```php
$users = Database::table('users')
    ->whereLike('name', '%Tom%')
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
WHERE name LIKE ?;
```

For an `OR LIKE` condition:

```php
$users = Database::table('users')
    ->whereLike('name', '%Tom%')
    ->orWhereLike('email', '%example.com')
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
WHERE name LIKE ?
OR email LIKE ?;
```

## First Result

Use `first()` when only the first matching record is needed:

```php
$user = Database::table('users')
    ->where('id', 1)
    ->first();
```

A matching record is returned as an associative array:

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

`first()` automatically compiles the query with a limit of one result.

## Limit

Use `limit()` to restrict the number of returned records:

```php
$users = Database::table('users')
    ->limit(5)
    ->get();
```

Conceptually:

```sql
SELECT * FROM users
LIMIT 5;
```

The limit must be greater than zero.

For example:

```php
Database::table('users')
    ->limit(0);
```

causes a `DatabaseException`.

## Insert

Insert a record with:

```php
$query = Database::table('users');

$result = $query->insert([
    'name' => 'Tom',
    'email' => 'tom@example.com',
]);
```

Conceptually:

```sql
INSERT INTO users (name, email)
VALUES (?, ?);
```

`insert()` returns the result of the PDO statement execution.

An empty insert is not allowed:

```php
Database::table('users')
    ->insert([]);
```

This causes a `DatabaseException`.

## Last Insert ID

After an insert, the generated identifier can be obtained from the same Query Builder instance:

```php
$query = Database::table('users');

$query->insert([
    'name' => 'Tom',
    'email' => 'tom@example.com',
]);

$id = $query->lastInsertId();
```

`lastInsertId()` returns the value provided by PDO.

This method is also used internally by `ActiveRecord` when inserting a new record.

## Update

Updates require at least one `WHERE` condition:

```php
Database::table('users')
    ->where('id', 1)
    ->update([
        'name' => 'Tom Garcia',
    ]);
```

Conceptually:

```sql
UPDATE users
SET name = ?
WHERE id = ?;
```

An empty update is rejected:

```php
Database::table('users')
    ->where('id', 1)
    ->update([]);
```

An update without `WHERE` is also rejected:

```php
Database::table('users')
    ->update([
        'active' => 0,
    ]);
```

Both cases cause a `DatabaseException`.

This protection prevents accidental updates of every row in a table.

## Delete

Deletes also require at least one `WHERE` condition:

```php
Database::table('users')
    ->where('id', 1)
    ->delete();
```

Conceptually:

```sql
DELETE FROM users
WHERE id = ?;
```

The following operation is intentionally rejected:

```php
Database::table('users')
    ->delete();
```

This protection prevents accidental deletion of every row in a table.

## Query Example

```php
$users = Database::table('users')
    ->select([
        'id',
        'name',
    ])
    ->where('id', 5, '>')
    ->whereLike('name', '%Tom%')
    ->limit(10)
    ->get();
```

The Query Builder stores the query structure and bindings separately.

The active Grammar then compiles the SQL for the configured database driver.

## Prepared Statements

Values used in conditions and data operations are not concatenated directly into SQL.

For example:

```php
Database::table('users')
    ->where('email', $email)
    ->first();
```

is compiled using a placeholder:

```sql
WHERE email = ?
```

and `$email` is sent separately as a binding.

The same approach is used for:

- `where()`;
- `orWhere()`;
- `whereLike()`;
- `orWhereLike()`;
- `insert()`;
- `update()`;
- `delete()` conditions.

## Identifier Validation

Table and column identifiers are validated by the database grammars.

Valid identifiers follow the supported format:

```text
users
user_profiles
created_at
users.email
```

Invalid identifiers are rejected before SQL execution.

For example, arbitrary SQL cannot be supplied as a table or column identifier through the Query Builder.

This validation is separate from prepared statements because SQL identifiers cannot be bound using normal PDO value placeholders.

## Database Grammars

The Query Builder delegates SQL generation to the Grammar associated with the active driver:

```text
mysql
  ↓
MySQLGrammar

pgsql
  ↓
PostgresGrammar

sqlite
  ↓
SQLiteGrammar
```

The grammars are responsible for:

- quoting identifiers;
- validating identifiers;
- compiling SELECT;
- compiling INSERT;
- compiling UPDATE;
- compiling DELETE;
- compiling WHERE conditions;
- compiling LIMIT.

This allows application code to use the same Query Builder API across the supported database systems.

## Under the Hood

```text
Application
    ↓
Database::table()
    ↓
QueryBuilder
    ↓
Grammar
    ├── MySQLGrammar
    ├── PostgresGrammar
    └── SQLiteGrammar
    ↓
Prepared SQL + Bindings
    ↓
PDO
    ↓
Database
```

## Safety Rules

The current Query Builder intentionally applies several protections:

```text
Invalid SQL operator
    → rejected

Invalid table/column identifier
    → rejected

Empty SELECT column list
    → rejected

LIMIT <= 0
    → rejected

Empty INSERT
    → rejected

Empty UPDATE
    → rejected

UPDATE without WHERE
    → rejected

DELETE without WHERE
    → rejected
```

These rules reduce accidental destructive operations and keep SQL structure controlled by the package.

## Next Step

To execute multiple operations as a transaction, see:

[Transactions →](transactions.md)