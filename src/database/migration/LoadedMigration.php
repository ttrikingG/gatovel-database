<?php

namespace Gatovel\Database\migration;

class LoadedMigration
{
    public function __construct(
        private string $name,
        private Migration $migration
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function migration(): Migration
    {
        return $this->migration;
    }
}
