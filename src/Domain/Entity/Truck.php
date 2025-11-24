<?php
namespace App\Domain\Entity;

class Truck extends Vehicle
{
    public function __construct(string $plate)
    {
        parent::__construct($plate, 'truck');
    }
}