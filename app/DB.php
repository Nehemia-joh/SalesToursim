<?php
/** DB.php - PDO singleton connected through the .envSalesTourism credentials. */
final class DB
{
    /** @var PDO|null */
    private static $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) return self::$pdo;

        $dsn_host = Env::get('DB_HOST', '127.0.0.1');
        $dsn_port = Env::int('DB_PORT', 3306);
        $name     = Env::get('DB_NAME', '');
        $user     = Env::get('DB_USER', 'root');
        $pass     = Env::get('DB_PASS', '');
        $charset  = Env::get('DB_CHARSET', 'utf8mb4');

        if ($name === '') {
            http_response_code(500);
            exit('DB_NAME is missing in .envSalesTourism');
        }

        $dsn = "mysql:host={$dsn_host};port={$dsn_port};dbname={$name};charset={$charset}";
        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('DB connect failed: ' . $e->getMessage());
            exit('Database connection failed - check the credentials in .envSalesTourism');
        }
        return self::$pdo;
    }

    /** Shorthand: run a prepared query, return all rows. */
    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Shorthand: run a prepared query, return the first row (or null). */
    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /** Shorthand: single scalar value. */
    public static function val(string $sql, array $params = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    /** Shorthand: execute UPDATE/INSERT/DELETE. */
    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }
}
