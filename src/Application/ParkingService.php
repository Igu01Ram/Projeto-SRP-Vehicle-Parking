<?php
namespace App\Application;

use App\Domain\Entity\Car;
use App\Domain\Entity\Motorcycle;
use App\Domain\Entity\Truck;
use App\Domain\Interface\VehicleRepository;
use App\Domain\Interface\ParkingRepository;
use App\Domain\Service\CarPricing;
use App\Domain\Service\MotorcyclePricing;
use App\Domain\Service\TruckPricing;

class ParkingService
{
    private ParkingRepository $parkingRepo;
    private VehicleRepository $vehicleRepo;

    public function __construct(VehicleRepository $vehicleRepo, ParkingRepository $parkingRepo) {
        $this->vehicleRepo = $vehicleRepo;
        $this->parkingRepo = $parkingRepo;
    }

    public function entry(string $plate, string $type): void
    {
        $vehicle = $this->vehicleRepo->findByPlate($plate);

        if (!$vehicle) {
            $vehicle = $this->createVehicle($plate, $type);
            $this->vehicleRepo->save($vehicle);
        }

        $active = $this->parkingRepo->searchVehicle($plate);
        if ($active) {
            throw new \Exception("Vehicle is already parked!");
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->parkingRepo->registerEntry($plate, $now);
    }

    public function exit(string $plate): float
    {
        $active = $this->parkingRepo->searchVehicle($plate);
        if (!$active) {
            throw new \Exception("No active vehicles parked!");
        }

        $vehicle = $this->vehicleRepo->findByPlate($plate);
        if (!$vehicle) {
            throw new \Exception("Vehicle not found!");
        }

        $entry = new \DateTimeImmutable($active['entry']);
        $exit = new \DateTimeImmutable();

        $strategy = $this->getPricingRule($vehicle->getType());
        $value = $strategy->calculate($entry, $exit);

        $this->parkingRepo->registerExit(
            $plate,
            $exit->format('Y-m-d H:i:s'),
            $value
        );

        return $value;
    }

    public function report(): array
    {
        $data = $this->parkingRepo->showCompleted();
        return $this->groupByType($data);
    }

    private function createVehicle(string $plate, string $type)
    {
        return match ($type) {
            'car' => new Car($plate),
            'motorcycle' => new Motorcycle($plate),
            'truck' => new Truck($plate),
            default => throw new \Exception("Invalid type: {$type}")
        };
    }

    private function getPricingRule(string $type)
    {
        return match ($type) {
            'car' => new CarPricing(),
            'motorcycle' => new MotorcyclePricing(),
            'truck' => new TruckPricing(),
            default => throw new \Exception("Unknowed type: {$type}")
        };
    }

    private function groupByType(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $type = $item['type'];
            if (!isset($result[$type])) {
                $result[$type] = ['total' => 0, 'billing' => 0.0];
            }
            $result[$type]['total']++;
            $result[$type]['billing'] += $item['value'];
        }
        return $result;
    }
}
