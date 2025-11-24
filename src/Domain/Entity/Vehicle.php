<?php
namespace App\Domain\Entity;

abstract class Vehicle
{
    protected string $plate;
    protected string $type;

    public function __construct(string $plate, string $type)
    {
        $this->plate = $plate;
        $this->type = $type;
    }

    public function getPlate(): string
    {
        return $this->plate;
    }

    public function getType(): string
    {
        return $this->type;
    }

    abstract public function showInfo(): string;
}



