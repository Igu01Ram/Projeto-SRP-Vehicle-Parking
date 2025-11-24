<?php
namespace App\Domain\Interface;

use App\Domain\Entity\Vehicle;

interface VehicleRepository
{
    public function save(Vehicle $vehicle): void;
    public function findByPlate(string $plate): ?Vehicle;
    public function listAll(): array;
}


