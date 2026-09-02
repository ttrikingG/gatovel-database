<?php

namespace Gatovel\Database\migration;

class MigrationLoader
{
    public function load(string $directory): array
    {
        $migrations = [];

        foreach (glob($directory . '/*.php') as $file) {

            require_once $file;

            $class = pathinfo($file, PATHINFO_FILENAME);

            $className = __NAMESPACE__ . '\\' . $class;

            if (!class_exists($className)) {
                continue;
            }

            if (!is_subclass_of($className, Migration::class)) {
                continue;
            }

            $migrations[] = new $className();
        }

        return $migrations;
    }
}