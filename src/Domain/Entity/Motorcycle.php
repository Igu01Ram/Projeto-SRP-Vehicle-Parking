<?php
namespace App\Domain\Entity;

class Motorcycle extends Vehicle
{
    public function __construct(string $plate)
    {
        parent::__construct($plate, 'motorcycle');
    }

    public function showInfo(): string
    {
        return "Motorbike - Plate: {$this->plate}";
    }
}