<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Session\Session;
use Framework\Translation\Translator;

final readonly class SetLocale implements Middleware
{
    public function __construct(private Session $session, private Translator $translator)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        $locale = $this->session->get('locale');

        if (is_string($locale) && array_key_exists($locale, config('app.locales', []))) {
            $this->translator->setLocale($locale);
        }

        return $next($request);
    }
}
