<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\GeneratorCommand;
use Framework\Support\Str;

#[AsCommand('make:migration', 'Create a new migration file', '<name> [--create=table] [--table=table]')]
final class MakeMigrationCommand extends GeneratorCommand
{
    protected function build(string $name): array
    {
        $name = Str::snake($name);
        $create = $this->option('create');
        $table = $this->option('table');

        // "create_posts_table" implies --create=posts.
        if (!is_string($create) && !is_string($table) && preg_match('/^create_(\w+)_table$/', $name, $matches)) {
            $create = $matches[1];
        }

        $body = match (true) {
            is_string($create) => $this->createStub($create),
            is_string($table) => $this->alterStub($table),
            default => $this->blankStub(),
        };

        return [
            'path' => 'database/migrations/' . date('Y_m_d_His') . "_$name.php",
            'contents' => <<<PHP
                <?php

                declare(strict_types=1);

                use Framework\Database\Migrations\Migration;
                use Framework\Database\Schema\Blueprint;
                use Framework\Database\Schema\Schema;

                return new class extends Migration {
                $body
                };

                PHP,
        ];
    }

    private function createStub(string $table): string
    {
        return <<<PHP
                public function up(Schema \$schema): void
                {
                    \$schema->create('$table', function (Blueprint \$table): void {
                        \$table->id();
                        \$table->timestamps();
                    });
                }

                public function down(Schema \$schema): void
                {
                    \$schema->dropIfExists('$table');
                }
            PHP;
    }

    private function alterStub(string $table): string
    {
        return <<<PHP
                public function up(Schema \$schema): void
                {
                    \$schema->table('$table', function (Blueprint \$table): void {
                        //
                    });
                }

                public function down(Schema \$schema): void
                {
                    \$schema->table('$table', function (Blueprint \$table): void {
                        //
                    });
                }
            PHP;
    }

    private function blankStub(): string
    {
        return <<<'PHP'
                public function up(Schema $schema): void
                {
                    //
                }

                public function down(Schema $schema): void
                {
                    //
                }
            PHP;
    }
}
