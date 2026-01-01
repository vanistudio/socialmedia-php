<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
class DB
{
    private static ?PDO $pdo = null;
    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $host = $_ENV['APP_DB_HOST'];
            $db   = $_ENV['APP_DB_NAME'];
            $user = $_ENV['APP_DB_USER'];
            $pass = $_ENV['APP_DB_PASS'];
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$pdo;
    }
    public static function exec(string $sql): int
    {
        return self::conn()->exec($sql);
    }
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public static function first(string $sql, array $params = []): ?array
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function insert(string $sql, array $params = []): string
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return self::conn()->lastInsertId();
    }
    public static function transaction(callable $callback)
    {
        try {
            self::conn()->beginTransaction();
            $result = $callback();
            self::conn()->commit();
            return $result;
        } catch (Throwable $e) {
            self::conn()->rollBack();
            throw $e;
        }
    }
    public static function tableExists(string $table): bool
    {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = self::conn()->prepare($sql);
        $stmt->execute([$table]);
        return (bool) $stmt->fetch();
    }
}
