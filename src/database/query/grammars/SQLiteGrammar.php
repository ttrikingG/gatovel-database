<?php

namespace Gatovel\Database\query\grammars;

use Gatovel\Database\query\Grammar;

class SQLiteGrammar implements Grammar
{
    public function compileSelect(
        string $table,
        array $columns,
        array $wheres,
        ?int $limit = null
    ): string {
        $columns = empty($columns)
            ? '*'
            : implode(', ', $columns);

        $sql = "SELECT {$columns} FROM {$table}";

        if (!empty($wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        return $sql;
    }

    public function compileInsert(
        string $table,
        array $data
    ): string {
        $columns = implode(', ', array_keys($data));

        $placeholders = implode(
            ', ',
            array_fill(0, count($data), '?')
        );

        return "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    }

    public function compileUpdate(
        string $table,
        array $data,
        array $wheres
    ): string {
        $columns = implode(
            ', ',
            array_map(
                fn ($column) => "{$column} = ?",
                array_keys($data)
            )
        );

        $sql = "UPDATE {$table} SET {$columns}";

        if (!empty($wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }

        return $sql;
    }

    public function compileDelete(
        string $table,
        array $wheres
    ): string {
        $sql = "DELETE FROM {$table}";

        if (!empty($wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }

        return $sql;
    }
}
