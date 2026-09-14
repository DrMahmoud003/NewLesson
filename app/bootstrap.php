<?php
declare(strict_types=1);

use Alprof\Core\Database;
use Alprof\Core\Auth;
use Alprof\Core\Csrf;
use Alprof\Services\AppLogger;

const BASE_PATH = __DIR__ . '/..';

$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) { require $composerAutoload; }

spl_autoload_register(function (string $class): void {
    $prefix = 'Alprof\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

function envv(string $key, ?string $default = null): ?string
{
    static $env;
    if ($env === null) {
        $env = [];
        $file = BASE_PATH . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = array_map('trim', explode('=', $line, 2));
                $env[$name] = trim($value, "\"'");
            }
        }
    }
    return $env[$key] ?? $default;
}

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name(envv('SESSION_NAME', 'alprof_session'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

$db = Database::connection();
$auth = new Auth($db);
$csrf = new Csrf();

set_exception_handler(static function (\Throwable $e): void {
    AppLogger::instance()->error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'حدث خطأ غير متوقع؛ حاول مرة أخرى أو تواصل مع مدير النظام.';
});
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    AppLogger::instance()->warning($message, ['file' => $file, 'line' => $line]);
    return true;
});
