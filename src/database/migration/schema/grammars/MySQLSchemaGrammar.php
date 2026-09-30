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
            fn (array $column): string => $this->compileColumn($column),
            $columns
        );

        foreach ($blueprint->indexes() as $index) {
            $definitions[] = $this->compileIndex($index);
        }

        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->wrapIdentifier($blueprint->table()),
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
        $this->validateColumn($column);

        $name = $this->wrapIdentifier(
            $column['name']
        );

        if ($column['type'] === 'id') {
            return "{$name} BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        }

        $options = $column['options'];

        $type = match ($column['type']) {
            'string' => sprintf(
                'VARCHAR(%d)',
                $options['length']
            ),
            'text' => 'TEXT',
            'integer' => 'INT',
            'boolean' => 'TINYINT(1)',
            'timestamp' => 'TIMESTAMP',
            default => throw new DatabaseException(
                "Tipo de coluna não suportado: {$column['type']}"
            ),
        };

        $definition = "{$name} {$type}";

        $definition .= ($options['nullable'] ?? false)
            ? ' NULL'
            : ' NOT NULL';

        if ($options['default_current'] ?? false) {
            $definition .= ' DEFAULT CURRENT_TIMESTAMP';
        } elseif ($options['has_default'] ?? false) {
            $definition .= ' DEFAULT '
                . $this->compileDefault(
                    $options['default'] ?? null
                );
        }

        return $definition;
    }

    private function compileIndex(
        array $index
    ): string {
        $columns = implode(
            ', ',
            array_map(
                fn (string $column): string
                    => $this->wrapIdentifier($column),
                $index['columns']
            )
        );

        return match ($index['type']) {
            'primary' => "PRIMARY KEY ({$columns})",

            'unique' => $index['name'] !== null
                ? sprintf(
                    'CONSTRAINT %s UNIQUE (%s)',
                    $this->wrapIdentifier($index['name']),
                    $columns
                )
                : "UNIQUE ({$columns})",

            default => throw new DatabaseException(
                "Tipo de índice não suportado: {$index['type']}"
            ),
        };
    }

    private function compileDefault(
        mixed $value
    ): string {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return "'" . str_replace("'", "''", $value) . "'";
        }

        throw new DatabaseException(
            'Valor padrão de coluna não suportado.'
        );
    }

    private function validateColumn(
        array $column
    ): void {
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
