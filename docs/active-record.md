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
Connection
  ↓
PDO
  ↓
Database
```

Active Record is built on top of the existing Query Builder. It does not replace the database layer.

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

## Creating a Record

Create a new model:

```php
$user = new User();

$user->name = 'Tom Garcia';
$user->email = 'tom@example.com';

$user->save();
```

The `save()` method inserts the record when it does not have a primary key.

The generated ID is automatically assigned to the model:

```php
echo $user->id;
```

## Finding a Record

Use `find()` to retrieve a record by its primary key:

```php
$user = User::find(1);
```

If the record exists, an instance of the model is returned.

If it does not exist:

```php
null
```

Example:

```php
$user = User::find(1);

if ($user === null) {
    echo 'User not found.';
    return;
}

echo $user->name;
echo $user->email;
```

## Retrieving All Records

Use `all()`:

```php
$users = User::all();
```

The result is an array containing the database records.

Example:

```php
$users = User::all();

foreach ($users as $user) {
    echo $user['name'] . PHP_EOL;
}
```

## Where Queries

The model can also start a Query Builder query:

```php
$users = User::where('name', 'Tom')->get();
```

Multiple conditions can be used:

```php
$users = User::where('name', 'Tom')
    ->where('email', 'tom@example.com')
    ->get();
```

Custom operators are supported:

```php
$users = User::where('id', 10, '>')->get();
```

Because `where()` returns the Query Builder, all supported Query Builder methods can be chained.

## Updating a Record

Retrieve a model and modify its attributes:

```php
$user = User::find(1);

if ($user !== null) {
    $user->name = 'Tom Garcia';
    $user->save();
}
```

When the model already has its primary key, `save()` performs an update instead of an insert.

## Deleting a Record

Retrieve the model:

```php
$user = User::find(1);

if ($user !== null) {
    $user->delete();
}
```

The record associated with the model's primary key is deleted.

## Primary Key

The default primary key is:

```php
protected static string $primaryKey = 'id';
```

If a model uses a different primary key, it can override the property:

```php
class Product extends ActiveRecord
{
    protected static string $table = 'products';

    protected static string $primaryKey = 'product_id';
}
```

## Attributes

Model attributes can be accessed using normal property syntax:

```php
$user->name = 'Tom';
$user->email = 'tom@example.com';
```

Read attributes in the same way:

```php
echo $user->name;
echo $user->email;
```

Internally, Active Record stores these values as model attributes.

## Complete Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

use app\models\User;

// Create
$user = new User();

$user->name = 'Tom Garcia';
$user->email = 'tom@example.com';

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

// Query
$users = User::where('name', 'Tom Garcia Updated')->get();

foreach ($users as $user) {
    echo $user['email'] . PHP_EOL;
}

// Delete
$user->delete();
```

## Active Record vs Query Builder

Both APIs are available and serve different purposes.

### Query Builder

Use the Query Builder when you want direct query construction:

```php
$users = Database::table('users')
    ->where('name', 'Tom')
    ->get();
```

### Active Record

Use Active Record when working with application models:

```php
$user = User::find(1);

$user->name = 'Tom Garcia';

$user->save();
```

The two APIs work together:

```text
Active Record
      ↓
Query Builder
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

More advanced ORM functionality is outside the scope of the core database package.

## Next Step

You have now completed the main Gatovel Database documentation.

Return to the documentation index:

[← Documentation](README.md)
