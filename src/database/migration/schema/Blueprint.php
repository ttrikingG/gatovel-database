<?php

namespace Gatovel\Database\migration\schema;

use Gatovel\Database\exceptions\DatabaseException;

class Blueprint
{
    private string $table;

    private array $columns = [];

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
        int $length = 255
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
            ]
        );
    }

    public function text(
        string $name
    ): static {
        return $this->addColumn(
            $name,
            'text'
        );
    }

    public function integer(
        string $name
    ): static {
        return $this->addColumn(
            $name,
            'integer'
        );
    }

    public function boolean(
        string $name
    ): static {
        return $this->addColumn(
            $name,
            'boolean'
        );
    }

    public function timestamp(
        string $name
    ): static {
        return $this->addColumn(
            $name,
            'timestamp'
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
