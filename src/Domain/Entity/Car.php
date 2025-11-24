<?php
namespace App\Domain\Entity;

class Car extends Vehicle
{
    public function __construct(string $plate)
    {
        parent::__construct($plate, 'car');
    }
}