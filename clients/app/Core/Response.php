<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $html, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function redirect(string $to, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $to);
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>404</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    }
}
