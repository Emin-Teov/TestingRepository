<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\GeneratorCommand;

#[AsCommand('make:controller', 'Create a new controller class', '<Name> (e.g. Admin/PostController)')]
final class MakeControllerCommand extends GeneratorCommand
{
    protected function build(string $name): array
    {
        [$namespace, $class] = $this->qualify($name, 'App\\Controllers');

        return [
            'path' => 'app/' . str_replace('\\', '/', substr($namespace, 4)) . "/$class.php",
            'contents' => <<<PHP
                <?php

                declare(strict_types=1);

                namespace $namespace;

                use Framework\Http\Request;
                use Framework\Http\Response;

                final class $class
                {
                    public function index(Request \$request): Response
                    {
                        return view('home');
                    }
                }

                PHP,
        ];
    }
}
