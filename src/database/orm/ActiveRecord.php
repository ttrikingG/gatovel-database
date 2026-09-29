<?php

namespace Gatovel\Database\orm;

use Gatovel\Database\Database;
use Gatovel\Database\query\QueryBuilder;

abstract class ActiveRecord
{
    protected static string $table;

    protected static string $primaryKey = 'id';

    protected array $attributes = [];

    public function __construct(
        array $attributes = []
    ) {
        $this->attributes = $attributes;
    }

    public function __get(
        string $name
    ): mixed {
        return $this->attributes[$name] ?? null;
    }

    public function __set(
        string $name,
        mixed $value
    ): void {
        $this->attributes[$name] = $value;
    }

    public static function find(
        int|string $id
    ): ?static {
        $data = Database::table(
            static::$table
        )
            ->where(
                static::$primaryKey,
                $id
            )
            ->first();

        if ($data === null) {
            return null;
        }

        return new static(
            $data
        );
    }

    public static function all(): array
    {
        $records = Database::table(
            static::$table
        )
            ->select()
            ->get();

        return array_map(
            static fn (array $attributes): static
                => new static($attributes),
            $records
        );
    }

    public static function where(
        string $column,
        mixed $value,
        string $operator = '='
    ): QueryBuilder {
        return Database::table(
            static::$table
        )
            ->where(
                $column,
                $value,
                $operator
            );
    }

    public function save(): bool
    {
        $primaryKey = static::$primaryKey;

        $isNewRecord =
            !array_key_exists(
                $primaryKey,
                $this->attributes
            )
            || $this->attributes[$primaryKey] === null;

        if ($isNewRecord) {
            $data = $this->attributes;

            unset(
                $data[$primaryKey]
            );

            $query = Database::table(
                static::$table
            );

            $result = $query->insert(
                $data
            );

            if ($result) {
                $id = $query->lastInsertId();

                if ($id !== '') {
                    $this->attributes[$primaryKey] =
                        is_numeric($id)
                            ? (int) $id
                            : $id;
                }
            }

            return $result;
        }

        $id = $this->attributes[$primaryKey];

        $data = $this->attributes;

        unset(
            $data[$primaryKey]
        );

        return Database::table(
            static::$table
        )
            ->where(
                $primaryKey,
                $id
            )
            ->update(
                $data
            );
    }

    public function delete(): bool
    {
        $primaryKey = static::$primaryKey;

        if (
            !array_key_exists(
                $primaryKey,
                $this->attributes
            )
            || $this->attributes[$primaryKey] === null
        ) {
            return false;
        }

        $result = Database::table(
            static::$table
        )
            ->where(
                $primaryKey,
                $this->attributes[$primaryKey]
            )
            ->delete();

        if ($result) {
            unset(
                $this->attributes[$primaryKey]
            );
        }

        return $result;
    }
}
