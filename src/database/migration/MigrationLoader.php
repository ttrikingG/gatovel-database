<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\exceptions\DatabaseException;

class MigrationLoader
{
    /**
     * @return LoadedMigration[]
     */
    public function load(
        string $directory
    ): array {
        if (!is_dir($directory)) {
            throw new DatabaseException(
                "Diretório de migrations não encontrado: {$directory}"
            );
        }

        $files = glob(
            rtrim($directory, '/\\') . '/*.php'
        );

        if ($files === false) {
            throw new DatabaseException(
                "Não foi possível carregar o diretório de migrations: {$directory}"
            );
        }

        sort(
            $files,
            SORT_STRING
        );

        $migrations = [];

        foreach ($files as $file) {
            $migration = require $file;

            if (!$migration instanceof Migration) {
                throw new DatabaseException(
                    "O arquivo não retornou uma Migration válida: {$file}"
                );
            }

            $name = pathinfo(
                $file,
                PATHINFO_FILENAME
            );

            $migrations[] = new LoadedMigration(
                $name,
                $migration
            );
        }

        return $migrations;
    }
}
