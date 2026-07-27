<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish wrapper around superglobals. Nothing in the app reads
 * $_GET/$_POST/$_SERVER directly outside this class, so input handling
 * (and sanitization boundaries) stays in one auditable place.
 */
final class Request
{
    /** @var array<string,mixed> */
    private array $query;

    /** @var array<string,mixed> */
    private array $body;

    /** @var array<string,mixed> */
    private array $server;

    /** @var array<string,array<string,mixed>> */
    private array $files;

    private string $method;

    private string $path;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $this->path = is_string($path) && $path !== '' ? $path : '/';
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($this->server[$key]) ? (string) $this->server[$key] : null;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function isJson(): bool
    {
        $type = $this->header('Content-Type') ?? '';

        return str_contains($type, 'application/json');
    }
}
