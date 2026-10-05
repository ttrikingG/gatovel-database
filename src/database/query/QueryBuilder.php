<?php

namespace Gatovel\Database\query;

use Closure;
use Gatovel\Database\exceptions\DatabaseException;
use PDO;

class QueryBuilder
{
    private const ALLOWED_OPERATORS = [
        '=',
        '!=',
        '<>',
        '>',
        '>=',
        '<',
        '<=',
        'LIKE',
        'NOT LIKE',
    ];

    private PDO $connection;

    private string $table;

    private Grammar $grammar;

    private array $wheres = [];

    private array $bindings = [];

    private array $columns = [];

    private ?int $limit = null;

    private ?int $offset = null;

    public function __construct(
        PDO $connection,
        string $table,
        Grammar $grammar
    ) {
        $this->connection = $connection;
        $this->table = $table;
        $this->grammar = $grammar;
    }

    public function select(
        array $columns = ['*']
    ): static {
        if ($columns === []) {
            throw new DatabaseException(
                'A seleção deve possuir pelo menos uma coluna.'
            );
        }

        $this->columns = $columns;

        return $this;
    }

    public function where(
        string $column,
        mixed $value,
        string $operator = '='
    ): static {
        return $this->addWhere(
            $column,
            $value,
            $operator,
            'AND'
        );
    }

    public function orWhere(
        string $column,
        mixed $value,
        string $operator = '='
    ): static {
        return $this->addWhere(
            $column,
            $value,
            $operator,
            'OR'
        );
    }

    public function whereLike(
        string $column,
        mixed $value
    ): static {
        return $this->where(
            $column,
            $value,
            'LIKE'
        );
    }

    public function orWhereLike(
        string $column,
        mixed $value
    ): static {
        return $this->orWhere(
            $column,
            $value,
            'LIKE'
        );
    }

    public function search(
        string $term,
        array $columns
    ): static {
        $term = trim($term);

        if ($term === '') {
            return $this;
        }

        if ($columns === []) {
            throw new DatabaseException(
                'A busca deve possuir pelo menos uma coluna.'
            );
        }

        return $this->whereGroup(
            function (QueryBuilder $query) use (
                $term,
                $columns
            ): void {
                $value = '%' . $term . '%';

                foreach ($columns as $index => $column) {
                    if (!is_string($column) || trim($column) === '') {
                        throw new DatabaseException(
                            'As colunas de busca devem possuir nomes válidos.'
                        );
                    }

                    if ($index === 0) {
                        $query->whereLike(
                            $column,
                            $value
                        );

                        continue;
                    }

                    $query->orWhereLike(
                        $column,
                        $value
                    );
                }
            }
        );
    }

    public function whereGroup(
        Closure $callback
    ): static {
        return $this->addWhereGroup(
            $callback,
            'AND'
        );
    }

    public function orWhereGroup(
        Closure $callback
    ): static {
        return $this->addWhereGroup(
            $callback,
            'OR'
        );
    }

    public function limit(
        int $limit
    ): static {
        if ($limit < 1) {
            throw new DatabaseException(
                'O limite deve ser maior que zero.'
            );
        }

        $this->limit = $limit;

        return $this;
    }

    public function offset(
        int $offset
    ): static {
        if ($offset < 0) {
            throw new DatabaseException(
                'O offset não pode ser negativo.'
            );
        }

        $this->offset = $offset;

        return $this;
    }

    public function get(): array
    {
        $sql = $this->grammar->compileSelect(
            $this->table,
            $this->columns,
            $this->wheres,
            $this->limit,
            $this->offset
        );

        $statement = $this->connection->prepare(
            $sql
        );

        $statement->execute(
            $this->bindings
        );

        return $statement->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function first(): ?array
    {
        $sql = $this->grammar->compileSelect(
            $this->table,
            $this->columns,
            $this->wheres,
            1
        );

        $statement = $this->connection->prepare(
            $sql
        );

        $statement->execute(
            $this->bindings
        );

        $result = $statement->fetch(
            PDO::FETCH_ASSOC
        );

        return $result === false
            ? null
            : $result;
    }

    public function count(): int
    {
        $sql = $this->grammar->compileCount(
            $this->table,
            $this->wheres
        );

        $statement = $this->connection->prepare(
            $sql
        );

        $statement->execute(
            $this->bindings
        );

        return (int) $statement->fetchColumn();
    }

    public function paginate(
        int $perPage = 15,
        int $page = 1
    ): array {
        if ($perPage < 1) {
            throw new DatabaseException(
                'A quantidade de registros por página deve ser maior que zero.'
            );
        }

        if ($page < 1) {
            throw new DatabaseException(
                'A página atual deve ser maior que zero.'
            );
        }

        $total = $this->count();

        $lastPage = max(
            1,
            (int) ceil($total / $perPage)
        );

        $offset = ($page - 1) * $perPage;

        $sql = $this->grammar->compileSelect(
            $this->table,
            $this->columns,
            $this->wheres,
            $perPage,
            $offset
        );

        $statement = $this->connection->prepare(
            $sql
        );

        $statement->execute(
            $this->bindings
        );

        $data = $statement->fetchAll(
            PDO::FETCH_ASSOC
        );

        $count = count($data);

        $from = $count === 0
            ? null
            : $offset + 1;

        $to = $count === 0
            ? null
            : $offset + $count;

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
            ],
        ];
    }

    public function insert(
        array $data
    ): bool {
        if ($data === []) {
            throw new DatabaseException(
                'Os dados para inserção não podem estar vazios.'
            );
        }

        $sql = $this->grammar->compileInsert(
            $this->table,
            $data
        );

        $statement = $this->connection->prepare(
            $sql
        );

        return $statement->execute(
            array_values($data)
        );
    }

    public function update(
        array $data
    ): bool {
        if ($data === []) {
            throw new DatabaseException(
                'Os dados para atualização não podem estar vazios.'
            );
        }

        if ($this->wheres === []) {
            throw new DatabaseException(
                'Atualização sem condição WHERE não é permitida.'
            );
        }

        $sql = $this->grammar->compileUpdate(
            $this->table,
            $data,
            $this->wheres
        );

        $bindings = array_merge(
            array_values($data),
            $this->bindings
        );

        $statement = $this->connection->prepare(
            $sql
        );

        return $statement->execute(
            $bindings
        );
    }

    public function delete(): bool
    {
        if ($this->wheres === []) {
            throw new DatabaseException(
                'Exclusão sem condição WHERE não é permitida.'
            );
        }

        $sql = $this->grammar->compileDelete(
            $this->table,
            $this->wheres
        );

        $statement = $this->connection->prepare(
            $sql
        );

        return $statement->execute(
            $this->bindings
        );
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    private function addWhere(
        string $column,
        mixed $value,
        string $operator,
        string $boolean
    ): static {
        $operator = strtoupper(
            trim($operator)
        );

        if (
            !in_array(
                $operator,
                self::ALLOWED_OPERATORS,
                true
            )
        ) {
            throw new DatabaseException(
                "Operador não permitido: {$operator}"
            );
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'boolean' => $boolean,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    private function addWhereGroup(
        Closure $callback,
        string $boolean
    ): static {
        $query = new static(
            $this->connection,
            $this->table,
            $this->grammar
        );

        $callback(
            $query
        );

        if ($query->wheres === []) {
            throw new DatabaseException(
                'O grupo WHERE não pode estar vazio.'
            );
        }

        $this->wheres[] = [
            'type' => 'group',
            'boolean' => $boolean,
            'wheres' => $query->wheres,
        ];

        $this->bindings = array_merge(
            $this->bindings,
            $query->bindings
        );

        return $this;
    }
}
