<?php
declare(strict_types=1);

const BASE_PATH = __DIR__;

if (!function_exists('envv')) {
    function envv(string $key, ?string $default = null): ?string
    {
        return $default;
    }
}

if (!function_exists('importRedirect')) {
    function importRedirect(string $type, string $message): never
    {
        exit;
    }
}

if (!function_exists('back')) {
    function back(string $type, string $msg, int $g, string $m): never
    {
        exit;
    }
}