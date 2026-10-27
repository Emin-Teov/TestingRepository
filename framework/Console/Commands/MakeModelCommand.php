<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\GeneratorCommand;
use Framework\Support\Str;

#[AsCommand('make:model', 'Create a new model class', '<Name> [--migration]')]
final class MakeModelCommand extends GeneratorCommand
{
    public function handle(): int
    {
        $status = parent::handle();

        if ($status === self::SUCCESS && $this->option('migration')) {
            $command = $this->app->make(MakeMigrationCommand::class);
            $command->setInput(['create_' . $this->tableName() . '_table']);

            return $command->handle();
        }

        return $status;
    }

    protected function build(string $name): array
    {
        [$namespace, $class] = $this->qualify($name, 'App\\Models');
        $table = $this->tableName();

        return [
            'path' => 'app/' . str_replace('\\', '/', substr($namespace, 4)) . "/$class.php",
            'contents' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace $namespace;

                use Framework\Database\Model;

                final class $class extends Model
                {
                    protected static string \$table = '$table';

                    protected static array \$fillable = [];

                    protected static array \$casts = [
                        'id' => 'int',
                        'created_at' => 'datetime',
                        'updated_at' => 'datetime',
                    ];
                }

                PHP,
        ];
    }

    private function tableName(): string
    {
        $class = basename(str_replace('\\', '/', (string) $this->argument(0)));

        return Str::snake($class) . 's';
    }
}
