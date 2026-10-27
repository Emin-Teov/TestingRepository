<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use Framework\Database\ModelNotFoundException;
use Framework\Foundation\Application;
use Framework\Http\JsonResponse;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Logging\Logger;
use Framework\Support\Arr;
use Framework\Validation\ValidationException;
use Throwable;

/**
 * Converts exceptions into HTTP responses: HTML pages for the browser, JSON for the API.
 */
final readonly class Handler
{
    private const NEVER_FLASH = ['password', 'password_confirmation', '_token', '_method'];

    public function __construct(private Application $app, private Logger $logger)
    {
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof ValidationException) {
            return $this->renderValidation($request, $exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            $exception = new HttpException(404);
        }

        if (!$exception instanceof HttpException) {
            $this->logger->error($exception);
        }

        $status = $exception instanceof HttpException ? $exception->status() : 500;
        $headers = $exception instanceof HttpException ? $exception->headers() : [];
        $message = $this->messageFor($status, $exception);

        if ($request->expectsJson()) {
            $payload = ['message' => $message];

            if ($this->app->isDebug() && $status === 500) {
                $payload['exception'] = $exception::class;
                $payload['file'] = $exception->getFile() . ':' . $exception->getLine();
            }

            return new JsonResponse($payload, $status, $headers);
        }

        return $this->renderHtml($status, $message, $exception, $headers);
    }

    private function renderValidation(Request $request, ValidationException $exception): Response
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
        }

        return (new RedirectResponse($request->previousUrl(url('/'))))
            ->withErrors($exception->errors())
            ->withInput(Arr::except($request->all(), self::NEVER_FLASH));
    }

    private function messageFor(int $status, Throwable $exception): string
    {
        if ($exception instanceof HttpException && $exception->getMessage() !== '') {
            return $exception->getMessage();
        }

        if ($status === 500 && $this->app->isDebug()) {
            return $exception->getMessage();
        }

        return $this->translate("errors.$status", 'Something went wrong.');
    }

    private function renderHtml(int $status, string $message, Throwable $exception, array $headers): Response
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';

        try {
            return view('errors/error', [
                'status' => $status,
                'message' => $message,
                'exception' => $this->app->isDebug() && $status === 500 ? $exception : null,
            ], $status)->withHeaders($headers);
        } catch (Throwable $viewError) {
            // The error page itself failed (e.g. the database is down): fall back to plain HTML.
            $this->logger->error($viewError);
            $body = '<h1>' . $status . '</h1><p>' . e($message) . '</p>';

            if ($this->app->isDebug()) {
                $body .= '<pre>' . e((string) $exception) . '</pre>';
            }

            return new Response($body, $status, $headers);
        }
    }

    private function translate(string $key, string $fallback): string
    {
        try {
            $line = __($key);

            return $line === $key ? $fallback : $line;
        } catch (Throwable) {
            return $fallback;
        }
    }
}
