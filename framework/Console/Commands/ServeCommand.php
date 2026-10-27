<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;

#[AsCommand('serve', 'Start the PHP development server', '[--host=127.0.0.1] [--port=8000]')]
final class ServeCommand extends Command
{
    public function handle(): int
    {
        $host = (string) $this->option('host', '127.0.0.1');
        $port = (string) $this->option('port', '8000');
        $public = $this->app->basePath('public');

        $this->info("Server running on http://$host:$port  (Ctrl+C to stop)");

        passthru(sprintf(
            '%s -S %s -t %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg("$host:$port"),
            escapeshellarg($public),
            escapeshellarg($public . DIRECTORY_SEPARATOR . 'index.php'),
        ), $status);

        return $status;
    }
}
