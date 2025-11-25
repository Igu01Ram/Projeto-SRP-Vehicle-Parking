<?php
namespace App\Infra\Repository;

use App\Domain\Interface\ParkingRepository;
use PDO;

class SQLiteParkingRepository implements ParkingRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->createTable();
    }

    private function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS stay (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                plate TEXT NOT NULL,
                entry TEXT NOT NULL,
                exit TEXT,
                value REAL,
                FOREIGN KEY(plate) REFERENCES vehicles(plate)
            )
        ");
    }

    public function registerEntry(string $plate, string $dateHour): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO stay (plate, entry)
            VALUES (:plate, :entry)
        ");
        $stmt->execute([
            ':plate' => $plate,
            ':entry' => $dateHour
        ]);
    }

    public function registerExit(string $plate, string $dateHour, float $value): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE stay
            SET exit = :exit, value = :value
            WHERE plate = :plate AND exit IS NULL
        ");
        $stmt->execute([
            ':exit' => $dateHour,
            ':value' => $value,
            ':plate' => $plate
        ]);
    }

    public function searchVehicle(string $plate): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stay
            WHERE plate = :plate AND exit IS NULL
            LIMIT 1
        ");
        $stmt->execute([':plate' => $plate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function showCompleted(): array
    {
        $stmt = $this->pdo->query("
            SELECT e.plate, v.type, e.entry, e.exit, e.value
            FROM stay e
            INNER JOIN vehicles v ON e.plate = v.plate
            WHERE e.exit IS NOT NULL
            ORDER BY e.exit DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}