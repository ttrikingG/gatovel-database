# Active Record

Active Record provides a simple model-based interface for working with database records.

Each application model represents a database table and can create, retrieve, update and delete records.

## Architecture

```text
Model
  ↓
ActiveRecord
  ↓
QueryBuilder
  ↓
Grammar
  ↓
PDO
  ↓
Database
```

Active Record is built on top of the Query Builder. It does not replace the database layer.

## Creating a Model

Application models should be stored in:

```text
src/app/models/
```

Example:

```text
src/
└── app/
    └── models/
        └── User.php
```

Create a model by extending `ActiveRecord`:

```php
<?php

namespace app\models;

use Gatovel\Database\orm\ActiveRecord;

class User extends ActiveRecord
{
    protected static string $table = 'users';
}
```

The `$table` property defines the database table represented by the model.

The default primary key is:

```text
id
```

## Attributes

Model attributes are stored internally by `ActiveRecord` and can be accessed using normal property syntax.

```php
$user = new User();

$user->name = 'Tom';
$user->email = 'tom@example.com';
```

Read them in the same way:

```php
echo $user->name;
echo $user->email;
```

A model can also be initialized with attributes:

```php
$user = new User([
    'name' => 'Tom',
    'email' => 'tom@example.com',
]);
```

## Creating a Record

Create a model and call `save()`:

```php
$user = new User();

$user->name = 'Tom Garcia';
$user->email = 'tom@example.com';

$result = $user->save();
```

When the model does not contain a primary key, `save()` performs an insert.

After a successful insert, the generated ID is assigned to the model when PDO provides one:

```php
echo $user->id;
```

You can also create the model with initial attributes:

```php
$user = new User([
    'name' => 'Tom Garcia',
    'email' => 'tom@example.com',
]);

$user->save();
```

## Finding a Record

Use `find()` to retrieve a record by its primary key:

```php
$user = User::find(1);
```

If the record exists, an instance of the model is returned.

For example:

```php
if ($user !== null) {
    echo $user->name;
    echo $user->email;
}
```

If no matching record exists:

```php
null
```

`find()` accepts integer or string primary-key values.

## Retrieving All Records

Use `all()`:

```php
$users = User::all();
```

`all()` returns an array of model objects.

Therefore:

```php
foreach ($users as $user) {
    echo $user->name . PHP_EOL;
}
```

Each item is an instance of the model class, not a raw database array.

Conceptually:

```text
Database rows
    ↓
ActiveRecord::all()
    ↓
User object
User object
User object
```

## Where Queries

A model can start a Query Builder query with `where()`:

```php
$users = User::where('name', 'Tom')
    ->get();
```

`where()` returns a `QueryBuilder`.

Because of that, results returned by `get()` in this case are associative arrays:

```php
foreach ($users as $user) {
    echo $user['name'] . PHP_EOL;
}
```

This is different from:

```php
User::all();
```

which returns model objects.

## Multiple Conditions

Because `where()` returns the Query Builder, its supported methods can be chained:

```php
$users = User::where('name', 'Tom')
    ->where('email', 'tom@example.com')
    ->get();
```

Custom operators are supported:

```php
$users = User::where('id', 10, '>')
    ->get();
```

The Query Builder can continue the query:

```php
$users = User::where('active', 1)
    ->orWhere('name', 'Tom')
    ->limit(10)
    ->get();
```

For the complete query API, see the Query Builder documentation.

## Updating a Record

Retrieve a model, change its attributes and call `save()`:

```php
$user = User::find(1);

if ($user !== null) {
    $user->name = 'Tom Garcia';
    $user->save();
}
```

When the model already contains its primary key, `save()` performs an update.

The primary key itself is removed from the update data and is used as the `WHERE` condition.

Conceptually:

```text
Model has primary key?
        ↓
       yes
        ↓
UPDATE ... WHERE primary_key = ?
```

This also benefits from the Query Builder protection that prevents updates without a `WHERE` condition.

## Deleting a Record

Retrieve the model and call `delete()`:

```php
$user = User::find(1);

if ($user !== null) {
    $user->delete();
}
```

The record is deleted using the model's primary key.

Conceptually:

```sql
DELETE FROM users
WHERE id = ?;
```

After a successful deletion, the primary-key attribute is removed from the model.

Calling `delete()` on a model without a primary key returns:

```php
false
```

This prevents Active Record from issuing an unrestricted delete.

## Saving After Delete

Because a successful `delete()` removes the primary key from the model, the object no longer represents a persisted row with that identifier.

If `save()` is called afterward, Active Record treats it as a new record and performs an insert using the remaining attributes.

## Primary Key

The default primary key is:

```php
protected static string $primaryKey = 'id';
```

A model can override it:

```php
class Product extends ActiveRecord
{
    protected static string $table = 'products';

    protected static string $primaryKey = 'product_id';
}
```

`find()`, `save()` and `delete()` use the configured primary key.

## Save Lifecycle

`save()` determines whether the model is new by checking its primary-key attribute.

```text
save()
  ↓
Primary key exists and is not null?
  ├── no
  │    ↓
  │  INSERT
  │    ↓
  │  lastInsertId()
  │    ↓
  │  Assign generated ID when available
  │
  └── yes
       ↓
     UPDATE
       ↓
     WHERE primary key = value
```

A missing primary key and a primary key explicitly set to `null` are both treated as a new record.

## Complete Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

use app\models\User;

// Create
$user = new User([
    'name' => 'Tom Garcia',
    'email' => 'tom@example.com',
]);

$user->save();

echo "Created user: {$user->id}" . PHP_EOL;

// Find
$user = User::find($user->id);

if ($user === null) {
    exit('User not found.');
}

echo $user->name . PHP_EOL;

// Update
$user->name = 'Tom Garcia Updated';
$user->save();

// Query Builder through Active Record
$records = User::where('name', 'Tom Garcia Updated')
    ->get();

foreach ($records as $record) {
    echo $record['email'] . PHP_EOL;
}

// Model objects
$users = User::all();

foreach ($users as $model) {
    echo $model->name . PHP_EOL;
}

// Delete
$user->delete();
```

## Active Record vs Query Builder

Both APIs are available and serve different purposes.

### Query Builder

Use the Query Builder when direct query construction is desired:

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->get();
```

The Query Builder returns database rows as arrays.

### Active Record

Use Active Record when working with application models:

```php
$user = User::find(1);

if ($user !== null) {
    $user->name = 'Tom Garcia';
    $user->save();
}
```

Methods such as `find()` and `all()` create model objects.

The two APIs work together:

```text
Application Model
      ↓
ActiveRecord
      ↓
QueryBuilder
      ↓
Database
```

## Responsibility

Active Record is intentionally kept simple.

It provides the basic operations needed by application models:

```text
Create
Read
Update
Delete
```

It is not intended to provide a complete ORM with relationships, eager loading or other advanced ORM features.

More advanced ORM functionality can remain outside the core database package.

## Related Documentation

For direct database queries:

[Query Builder →](query-builder.md)

For database structure:

[Migrations →](migrations.md)

Return to the documentation index:

[← Documentation](README.md)
