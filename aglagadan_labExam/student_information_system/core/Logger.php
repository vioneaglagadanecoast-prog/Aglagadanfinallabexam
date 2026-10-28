<?php
declare(strict_types=1);

final class Logger
{
    public static function write(array $config, string $level, string $event, array $context = []): void
    {
        $path = $config['logging']['file'] ?? null;
        if (!$path) return;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        // Never pass passwords or session tokens into context.
        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'event' => $event,
            'context' => $context,
        ];
        @file_put_contents($path, json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
