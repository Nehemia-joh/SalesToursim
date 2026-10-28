<?php
/**
 * Env.php - tiny stand-alone .env loader (no composer required).
 *
 * Search order (first match wins):
 *   1. <one level above WebVersion>/.envWebVersion   <- canonical file
 *   2. <one level above WebVersion>/.envSalesTourism <- legacy
 *   3. <one level above WebVersion>/.env             <- legacy
 *   4. WebVersion/.envWebVersion
 *   5. WebVersion/.env                               <- legacy
 */
final class Env
{
    /** @var array<string,string> */
    private static $values = [];
    /** @var bool */
    private static $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) return;
        self::$loaded = true;

        $candidates = [
            dirname(__DIR__, 2) . '/.envWebVersion',
            dirname(__DIR__, 2) . '/.envSalesTourism',
            dirname(__DIR__, 2) . '/.env',
            dirname(__DIR__) . '/.envWebVersion',
            dirname(__DIR__) . '/.env',
        ];

        foreach ($candidates as $file) {
            if (!is_file($file) || !is_readable($file)) continue;
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || $line[0] === ';') continue;
                $pos = strpos($line, '=');
                if ($pos === false) continue;
                $key = trim(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                // strip surrounding quotes (single or double)
                if (strlen($val) >= 2 && (($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))) {
                    $val = substr($val, 1, -1);
                } else {
                    // allow trailing inline comments on unquoted values
                    $hash = strpos($val, ' #');
                    if ($hash !== false) $val = trim(substr($val, 0, $hash));
                }
                self::$values[$key] = $val;
            }
            break; // first file found wins
        }
    }

    /** Get a value with optional default; real environment variables still take precedence. */
    public static function get(string $key, string $default = ''): string
    {
        self::load();
        $env = getenv($key);
        if ($env !== false && $env !== '') return $env;
        return self::$values[$key] ?? $default;
    }

    public static function int(string $key, int $default): int
    {
        $v = self::get($key);
        return $v === '' ? $default : (int)$v;
    }
}
