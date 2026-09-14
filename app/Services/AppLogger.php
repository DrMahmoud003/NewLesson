<?php
namespace Alprof\Services;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;

final class AppLogger
{
    private static ?Logger $logger = null;

    public static function instance(): Logger
    {
        if (self::$logger instanceof Logger) {
            return self::$logger;
        }

        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $logger = new Logger('alprof');
        $handler = new RotatingFileHandler($logDir . '/app.log', 14, Level::Warning);
        $handler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            'Y-m-d H:i:s',
            true
        ));
        $logger->pushHandler($handler);

        if ((string) envv('APP_ENV', 'production') !== 'production') {
            $logger->pushHandler(new RotatingFileHandler($logDir . '/debug.log', 7, Level::Debug));
        }

        self::$logger = $logger;
        return $logger;
    }
}