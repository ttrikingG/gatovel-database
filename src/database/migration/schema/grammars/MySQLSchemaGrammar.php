<?php

namespace Gatovel\Database\migration\schema\grammars;

use Gatovel\Database\exceptions\DatabaseException;
use Gatovel\Database\migration\schema\Blueprint;
use Gatovel\Database\migration\schema\SchemaGrammar;

class MySQLSchemaGrammar implements SchemaGrammar
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
            'id' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',

            'string' => sprintf(
                'VARCHAR(%d) NOT NULL',
                $column['options']['length']
            ),

            'text' => 'TEXT NOT NULL',

            'integer' => 'INT NOT NULL',

            'boolean' => 'TINYINT(1) NOT NULL',

            'timestamp' => 'TIMESTAMP NOT NULL',

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

        return '`' . $identifier . '`';
    }
}
