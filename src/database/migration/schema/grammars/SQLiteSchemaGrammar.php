<?php

namespace Gatovel\Database\migration\schema\grammars;

use Gatovel\Database\exceptions\DatabaseException;
use Gatovel\Database\migration\schema\Blueprint;
use Gatovel\Database\migration\schema\SchemaGrammar;

class SQLiteSchemaGrammar implements SchemaGrammar
{
    public function compileCreate(
        Blueprint $blueprint
    ): string {
        $columns = $blueprint->columns();

        if ($columns === []) {
            throw new DatabaseException(
                'Não é possível criar uma tabela sem colunas.'
            );
        }

        $definitions = array_map(
            fn (array $column): string
                => $this->compileColumn($column),
            $columns
        );

        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->wrapIdentifier(
                $blueprint->table()
            ),
            implode(', ', $definitions)
        );
    }

    public function compileDrop(
        string $table
    ): string {
        return sprintf(
            'DROP TABLE %s',
            $this->wrapIdentifier($table)
        );
    }

    public function compileAddColumn(
        string $table,
        array $column
    ): string {
        return sprintf(
            'ALTER TABLE %s ADD COLUMN %s',
            $this->wrapIdentifier($table),
            $this->compileColumn($column)
        );
    }

    private function compileColumn(
        array $column
    ): string {
        if (
            !isset(
                $column['name'],
                $column['type'],
                $column['options']
            )
            || !is_string($column['name'])
            || !is_string($column['type'])
            || !is_array($column['options'])
        ) {
            throw new DatabaseException(
                'Definição de coluna inválida.'
            );
        }

        $name = $this->wrapIdentifier(
            $column['name']
        );

        $type = match ($column['type']) {
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',

            'string' => sprintf(
                'VARCHAR(%d) NOT NULL',
                $column['options']['length']
            ),

            'text' => 'TEXT NOT NULL',

            'integer' => 'INTEGER NOT NULL',

            'boolean' => 'INTEGER NOT NULL',

            'timestamp' => 'DATETIME NOT NULL',

            default => throw new DatabaseException(
                "Tipo de coluna não suportado: {$column['type']}"
            ),
        };

        return "{$name} {$type}";
    }

    private function wrapIdentifier(
        string $identifier
    ): string {
        if (
            !preg_match(
                '/^[A-Za-z_][A-Za-z0-9_]*$/',
                $identifier
            )
        ) {
            throw new DatabaseException(
                "Identificador SQL inválido: {$identifier}"
            );
        }

        return '"' . $identifier . '"';
    }
}
