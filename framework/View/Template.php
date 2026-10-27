<?php

declare(strict_types=1);

namespace Framework\View;

use LogicException;
use Throwable;

/**
 * A single render pass. Inside a template, `$this` refers to this object:
 *
 *   <?php $this->extend('layouts/app', ['title' => 'Home']) ?>
 *   <?php $this->start('scripts') ?> ... <?php $this->stop() ?>
 *   <?= $this->section('content') ?>
 *   <?= $this->insert('partials/flash') ?>
 */
final class Template
{
    private ?string $layout = null;

    private array $layoutData = [];

    /** @var array<string, string> */
    private array $sections = [];

    /** @var list<string> */
    private array $openSections = [];

    public function __construct(private readonly View $view)
    {
    }

    public function render(string $name, array $data = []): string
    {
        $content = $this->evaluate($this->view->find($name), $data);

        if ($this->layout === null) {
            return $content;
        }

        // The child view's output becomes the "content" section of its layout.
        $layout = $this->layout;
        $this->layout = null;
        $this->sections['content'] ??= $content;

        return $this->render($layout, [...$data, ...$this->layoutData]);
    }

    public function extend(string $layout, array $data = []): void
    {
        $this->layout = $layout;
        $this->layoutData = $data;
    }

    public function start(string $name): void
    {
        $this->openSections[] = $name;
        ob_start();
    }

    public function stop(): void
    {
        $name = array_pop($this->openSections) ?? throw new LogicException('stop() called without a matching start().');
        $this->sections[$name] = ($this->sections[$name] ?? '') . ob_get_clean();
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    /**
     * Render a partial with its own data (it does not see the parent's variables).
     */
    public function insert(string $name, array $data = []): string
    {
        return $this->view->render($name, $data);
    }

    private function evaluate(string $__file, array $__data): string
    {
        $level = ob_get_level();
        ob_start();

        try {
            (function () use ($__file, $__data): void {
                extract($__data, EXTR_SKIP);
                require $__file;
            })();
        } catch (Throwable $exception) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $exception;
        }

        return (string) ob_get_clean();
    }
}
