<?php

namespace Gatovel\Database\migration\schema;

use Gatovel\Database\exceptions\DatabaseException;

class Blueprint
{
    private string $table;

    private array $columns = [];

    private array $indexes = [];

    public function __construct(
        string $table
    ) {
        $this->validateIdentifier(
            $table
        );

        $this->table = $table;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function columns(): array
    {
        return $this->columns;
    }

    public function indexes(): array
    {
        return $this->indexes;
    }

    public function id(
        string $name = 'id'
    ): static {
        return $this->addColumn(
            $name,
            'id'
        );
    }

    public function string(
        string $name,
        int $length = 255,
        bool $nullable = false,
        mixed $default = null
    ): static {
        if ($length < 1) {
            throw new DatabaseException(
                'O tamanho da coluna string deve ser maior que zero.'
            );
        }

        return $this->addColumn(
            $name,
            'string',
            [
                'length' => $length,
                'nullable' => $nullable,
                'default' => $default,
                'has_default' => func_num_args() >= 4,
            ]
        );
    }

    public function text(
        string $name,
        bool $nullable = false
    ): static {
        return $this->addColumn(
            $name,
            'text',
            [
                'nullable' => $nullable,
            ]
        );
    }

    public function integer(
        string $name,
        bool $nullable = false,
        ?int $default = null
    ): static {
        return $this->addColumn(
            $name,
            'integer',
            [
                'nullable' => $nullable,
                'default' => $default,
                'has_default' => func_num_args() >= 3,
            ]
        );
    }

    public function boolean(
        string $name,
        bool $nullable = false,
        ?bool $default = null
    ): static {
        return $this->addColumn(
            $name,
            'boolean',
            [
                'nullable' => $nullable,
                'default' => $default,
                'has_default' => func_num_args() >= 3,
            ]
        );
    }

    public function timestamp(
        string $name,
        bool $nullable = false,
        bool $defaultCurrent = false
    ): static {
        return $this->addColumn(
            $name,
            'timestamp',
            [
                'nullable' => $nullable,
                'default_current' => $defaultCurrent,
            ]
        );
    }

    public function timestamps(): static
    {
        $this->timestamp(
            'created_at'
        );

        $this->timestamp(
            'updated_at'
        );

        return $this;
    }

    public function primary(
        array $columns
    ): static {
        $this->validateColumns(
            $columns
        );

        $this->indexes[] = [
            'type' => 'primary',
            'columns' => array_values($columns),
            'name' => null,
        ];

        return $this;
    }

    public function unique(
        array $columns,
        ?string $name = null
    ): static {
        $this->validateColumns(
            $columns
        );

        if ($name !== null) {
            $this->validateIdentifier(
                $name
            );
        }

        $this->indexes[] = [
            'type' => 'unique',
            'columns' => array_values($columns),
            'name' => $name,
        ];

        return $this;
    }

    private function addColumn(
        string $name,
        string $type,
        array $options = []
    ): static {
        $this->validateIdentifier(
            $name
        );

        foreach ($this->columns as $column) {
            if ($column['name'] === $name) {
                throw new DatabaseException(
                    "A coluna \"{$name}\" já foi definida."
                );
            }
        }

        $this->columns[] = [
            'name' => $name,
            'type' => $type,
            'options' => $options,
        ];

        return $this;
    }

    private function validateColumns(
        array $columns
    ): void {
        if ($columns === []) {
            throw new DatabaseException(
                'Um índice deve possuir pelo menos uma coluna.'
            );
        }

        foreach ($columns as $column) {
            if (!is_string($column)) {
                throw new DatabaseException(
                    'O nome da coluna do índice deve ser uma string.'
                );
            }

            $this->validateIdentifier(
                $column
            );

            $exists = false;

            foreach ($this->columns as $definedColumn) {
                if ($definedColumn['name'] === $column) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                throw new DatabaseException(
                    "A coluna \"{$column}\" não foi definida."
                );
            }
        }
    }

    private function validateIdentifier(
        string $identifier
    ): void {
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
    }
}
