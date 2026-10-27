<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Session\Session;

final class RedirectResponse extends Response
{
    public function __construct(private readonly string $url, int $status = 302)
    {
        parent::__construct('', $status, ['Location' => $url]);
    }

    public function url(): string
    {
        return $this->url;
    }

    /**
     * Flash a value to the session for the next request, e.g. ->with('success', '...').
     */
    public function with(string $key, mixed $value): self
    {
        app(Session::class)->flash($key, $value);

        return $this;
    }

    public function withErrors(array $errors): self
    {
        return $this->with('_errors', $errors);
    }

    public function withInput(array $input): self
    {
        return $this->with('_old', $input);
    }
}
