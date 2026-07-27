<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Thin PDO wrapper. Every query goes through prepared statements — no
 * method on this class accepts raw interpolated SQL with user input.
 * This is the only class in the app allowed to hold a PDO instance.
 */
final class DbAdapter
{
    private static ?DbAdapter $instance = null;

    private PDO $pdo;

    private function __construct() {
        $this->pdo = \Database::getInstance();
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param array<int|string,mixed> $params
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * @param array<int|string,mixed> $params
     * @return array<string,mixed>|null
     */
    public function one(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<int|string,mixed> $params
     */
    public function value(string $sql, array $params = []): mixed
    {
        $row = $this->one($sql, $params);
        if ($row === null) {
            return null;
        }

        return reset($row);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            '`' . implode('`, `', $columns) . '`',
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $column => $value) {
            $params[':' . $column] = $value;
        }

        $this->query($sql, $params);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $where
     */
    public function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        $params = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = :set_{$column}";
            $params["set_{$column}"] = $value;
        }

        $whereParts = [];
        foreach ($where as $column => $value) {
            $whereParts[] = "`{$column}` = :where_{$column}";
            $params["where_{$column}"] = $value;
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            implode(' AND ', $whereParts)
        );

        return $this->query($sql, $params)->rowCount();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function beginTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
