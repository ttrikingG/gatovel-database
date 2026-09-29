<?php

namespace Gatovel\Database\migration\schema;

interface SchemaGrammar
{
    public function compileCreate(
        Blueprint $blueprint
    ): string;

    public function compileDrop(
        string $table
    ): string;

    public function compileAddColumn(
        string $table,
        array $column
    ): string;
}
