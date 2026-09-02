<?php

namespace Gatovel\Database\query;

interface Grammar
{
    public function compileSelect(
        string $table,
        array $columns,
        array $wheres,
        ?int $limit = null
    ): string;

    public function compileInsert(
        string $table,
        array $data
    ): string;

    public function compileUpdate(
        string $table,
        array $data,
        array $wheres
    ): string;

    public function compileDelete(
        string $table,
        array $wheres
    ): string;
}