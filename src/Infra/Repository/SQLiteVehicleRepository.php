<?php
namespace App\Infra\Repository;

use App\Domain\Entity\Vehicle;
use App\Domain\Entity\Car;
use App\Domain\Entity\Motorcycle;
use App\Domain\Entity\Truck;
use App\Domain\Interface\VehicleRepository;
use PDO;

class SQLiteVehicleRepository implements VehicleRepository
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
            CREATE TABLE IF NOT EXISTS vehicles (
                plate TEXT PRIMARY KEY,
                type TEXT NOT NULL
            )
        ");
    }

    public function save(Vehicle $vehicle): void
    {
        $stmt = $this->pdo->prepare("
            INSERT OR REPLACE INTO vehicles (plate, type)
            VALUES (:plate, :type)
        ");
        $stmt->execute([
            ':plate' => $vehicle->getPlate(),
            ':type' => $vehicle->getType()
        ]);
    }

    public function findByPlate(string $plate): ?Vehicle
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE plate = :plate");
        $stmt->execute([':plate' => $plate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM vehicles");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrate($row), $rows);
    }

    private function hydrate(array $row): Vehicle
    {
        return match ($row['type']) {
            'car' => new Car($row['plate']),
            'motorcycle' => new Motorcycle($row['plate']),
            'truck' => new Truck($row['plate']),
            default => throw new \Exception("Unknowed type: {$row['type']}")
        };
    }
}


