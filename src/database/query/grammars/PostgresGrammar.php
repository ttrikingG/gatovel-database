<?php

namespace Gatovel\Database\query\grammars;

use Gatovel\Database\exceptions\DatabaseException;
use Gatovel\Database\query\Grammar;

class PostgresGrammar implements Grammar
{
    public function compileSelect(
        string $table,
        array $columns,
        array $wheres,
        ?int $limit = null
    ): string {
        $table = $this->wrapIdentifier($table);

        $columns = $columns === []
            ? '*'
            : implode(
                ', ',
                array_map(
                    fn (string $column): string
                        => $column === '*'
                            ? '*'
                            : $this->wrapIdentifier($column),
                    $columns
                )
            );

        $sql = "SELECT {$columns} FROM {$table}";

        $sql .= $this->compileWheres(
            $wheres
        );

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        return $sql;
    }

    public function compileInsert(
        string $table,
        array $data
    ): string {
        $table = $this->wrapIdentifier($table);

        $columns = array_map(
            fn (string $column): string
                => $this->wrapIdentifier($column),
            array_keys($data)
        );

        $placeholders = array_fill(
            0,
            count($data),
            '?'
        );

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
    }

    public function compileUpdate(
        string $table,
        array $data,
        array $wheres
    ): string {
        $table = $this->wrapIdentifier($table);

        $sets = [];

        foreach (array_keys($data) as $column) {
            $sets[] = $this->wrapIdentifier(
                $column
            ) . ' = ?';
        }

        $sql = sprintf(
            'UPDATE %s SET %s',
            $table,
            implode(', ', $sets)
        );

        return $sql . $this->compileWheres(
            $wheres
        );
    }

    public function compileDelete(
        string $table,
        array $wheres
    ): string {
        $table = $this->wrapIdentifier($table);

        return "DELETE FROM {$table}"
            . $this->compileWheres(
                $wheres
            );
    }

    private function compileWheres(
        array $wheres
    ): string {
        if ($wheres === []) {
            return '';
        }

        $conditions = [];

        foreach ($wheres as $index => $where) {
            $column = $this->wrapIdentifier(
                $where['column']
            );

            $operator = $where['operator'];

            $boolean = $index === 0
                ? ''
                : ' ' . $where['boolean'] . ' ';

            $conditions[] = $boolean
                . $column
                . ' '
                . $operator
                . ' ?';
        }

        return ' WHERE '
            . implode('', $conditions);
    }

    private function wrapIdentifier(
        string $identifier
    ): string {
        if ($identifier === '') {
            throw new DatabaseException(
                'O identificador SQL não pode ser vazio.'
            );
        }

        $parts = explode(
            '.',
            $identifier
        );

        foreach ($parts as $part) {
            if (
                !preg_match(
                    '/^[A-Za-z_][A-Za-z0-9_]*$/',
                    $part
                )
            ) {
                throw new DatabaseException(
                    "Identificador SQL inválido: {$identifier}"
                );
            }
        }

        return implode(
            '.',
            array_map(
                static fn (string $part): string
                    => '"' . $part . '"',
                $parts
            )
        );
    }
}
