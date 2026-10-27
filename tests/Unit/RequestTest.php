<?php

declare(strict_types=1);

namespace Tests\Unit;

use Framework\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    private array $server;

    protected function setUp(): void
    {
        $this->server = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        $_POST = [];
    }

    public static function installations(): array
    {
        return [
            'domain root (hosting)' => ['/index.php', '/admin/items', '', '/admin/items'],
            'built-in server' => ['/index.php', '/', '', '/'],
            'XAMPP sub-folder via root .htaccess' => ['/Framework/public/index.php', '/Framework/admin', '/Framework', '/admin'],
            'document root pointed at public/' => ['/Framework/public/index.php', '/Framework/public/api/items', '/Framework/public', '/api/items'],
        ];
    }

    #[DataProvider('installations')]
    public function test_base_path_and_path_detection(string $script, string $uri, string $basePath, string $path): void
    {
        $_SERVER = ['REQUEST_METHOD' => 'GET', 'SCRIPT_NAME' => $script, 'REQUEST_URI' => $uri . '?page=2'];

        $request = Request::capture();

        $this->assertSame($basePath, $request->basePath);
        $this->assertSame($path, $request->path);
    }

    public function test_method_spoofing_from_forms(): void
    {
        $_SERVER = ['REQUEST_METHOD' => 'POST', 'SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/admin/items/1'];
        $_POST = ['_method' => 'delete'];

        $this->assertSame('DELETE', Request::capture()->method);
    }

    public function test_previous_url_only_trusts_same_host(): void
    {
        $request = Request::create('GET', '/', [], ['HTTP_HOST' => 'localhost:8000', 'HTTP_REFERER' => 'http://evil.test/x']);
        $this->assertSame('/fallback', $request->previousUrl('/fallback'));

        $request = Request::create('GET', '/', [], ['HTTP_HOST' => 'localhost:8000', 'HTTP_REFERER' => 'http://localhost:8000/admin']);
        $this->assertSame('http://localhost:8000/admin', $request->previousUrl('/fallback'));
    }
}
