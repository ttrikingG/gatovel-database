<?php

namespace Gatovel\Database\seeder;

class SeederRunner
{
    /**
     * @param Seeder[] $seeders
     */
    public function run(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->run();
        }
    }
}