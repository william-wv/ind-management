<?php

namespace Core\Database;

use Core\Constants\Constants;
use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getDatabaseConn(): PDO
    {
        if (self::$connection === null) {
            $user = $_ENV['DB_USERNAME'];
            $pwd  = $_ENV['DB_PASSWORD'];
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db   = $_ENV['DB_DATABASE'];

            self::$connection = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $pwd);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$connection;
    }

    /**
     * Runs $fn inside a single DB transaction, committing on success
     * and rolling back if it throws.
     */
    public static function transaction(callable $fn): mixed
    {
        $pdo = self::getDatabaseConn();
        $pdo->beginTransaction();

        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    public static function getConn(): PDO
    {
        $user = $_ENV['DB_USERNAME'];
        $pwd  = $_ENV['DB_PASSWORD'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];

        $pdo = new PDO('mysql:host=' . $host . ';port=' . $port, $user, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function create(): void
    {
        $sql = 'CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);
    }

    public static function drop(): void
    {
        $sql = 'DROP DATABASE IF EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);

        // The cached connection (if any) was pointing at the database we
        // just dropped; force the next getDatabaseConn() call to reconnect.
        self::$connection = null;
    }

    public static function migrate(): void
    {
        $sql = file_get_contents(Constants::databasePath()->join('schema.sql'));
        self::getDatabaseConn()->exec($sql);
    }

    public static function exec(string $sql): void
    {
        self::getDatabaseConn()->exec($sql);
    }
}
