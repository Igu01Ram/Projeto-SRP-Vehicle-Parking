<?php
namespace App\Domain\Interface;

interface ParkingRepository
{
    public function registerEntry(string $plate, string $dateHour): void;
    public function registerExit(string $plate, string $dateHour, float $value): void;
    public function searchVehicle(string $plate): ?array;
    public function showCompleted(): array;
}
