<?php

declare(strict_types=1);

namespace Framework\Http;

final class JsonResponse extends Response
{
    public function __construct(private readonly mixed $data, int $status = 200, array $headers = [])
    {
        parent::__construct(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $status,
            $headers + ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public function data(): mixed
    {
        return json_decode($this->content, true);
    }
}
