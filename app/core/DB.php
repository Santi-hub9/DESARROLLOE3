<?php
class DB {
    private static ?PDO $pdo = null;
    static function pdo(): PDO {
        if (!self::$pdo) {
            self::$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }
    static function all($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); return $s->fetchAll(); }
    static function one($sql, $p = [])  { $r = self::all($sql, $p); return $r[0] ?? null; }
    static function run($sql, $p = []): int { $s = self::pdo()->prepare($sql); $s->execute($p); return $s->rowCount(); }
    static function exec($sql, $p = []) { $s = self::pdo()->prepare($sql); $s->execute($p); return self::pdo()->lastInsertId(); }
}
