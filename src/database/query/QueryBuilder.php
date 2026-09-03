<?php

namespace Gatovel\Database\query;

use PDO;

class QueryBuilder
{
    private PDO $connection;

    private string $table;

    private Grammar $grammar;

    private array $wheres = [];

    private array $bindings = [];

    private array $columns = [];

    private ?int $limit = null;

    public function __construct(
        PDO $connection,
        string $table,
        Grammar $grammar
    ) {
        $this->connection = $connection;
        $this->table = $table;
        $this->grammar = $grammar;
    }

    public function select(array $columns = ['*']): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function where(
        string $column,
        mixed $value,
        string $operator = '='
    ): static {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function get(): array
    {
        $sql = $this->grammar->compileSelect(
            $this->table,
            $this->columns,
            $this->wheres,
            $this->limit
        );

        $statement = $this->connection->prepare($sql);

        $statement->execute($this->bindings);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $sql = $this->grammar->compileSelect(
            $this->table,
            $this->columns,
            $this->wheres,
            1
        );

        $statement = $this->connection->prepare($sql);

        $statement->execute($this->bindings);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function insert(array $data): bool
    {
        $sql = $this->grammar->compileInsert(
            $this->table,
            $data
        );

        $statement = $this->connection->prepare($sql);

        return $statement->execute(
            array_values($data)
        );
    }

    public function update(array $data): bool
    {
        $sql = $this->grammar->compileUpdate(
            $this->table,
            $data,
            $this->wheres
        );

        $bindings = array_merge(
            array_values($data),
            $this->bindings
        );

        $statement = $this->connection->prepare($sql);

        return $statement->execute($bindings);
    }

    public function delete(): bool
    {
        $sql = $this->grammar->compileDelete(
            $this->table,
            $this->wheres
        );

        $statement = $this->connection->prepare($sql);

        return $statement->execute($this->bindings);
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

}

