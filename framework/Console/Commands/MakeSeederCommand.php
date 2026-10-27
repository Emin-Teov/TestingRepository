<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\GeneratorCommand;
use Framework\Support\Str;

#[AsCommand('make:seeder', 'Create a new seeder class', '<Name>')]
final class MakeSeederCommand extends GeneratorCommand
{
    protected function build(string $name): array
    {
        $class = Str::studly(basename($name));

        return [
            'path' => "database/seeders/$class.php",
            'contents' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace Database\Seeders;

                use Framework\Database\Seeder;

                final class $class extends Seeder
                {
                    public function run(): void
                    {
                        //
                    }
                }

                PHP,
        ];
    }
}
