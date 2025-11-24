<?php
namespace App\Domain\Service;

use App\Domain\Interface\PricingRule;

class TruckPricing implements PricingRule
{
    private const HOUR_VALUE = 10.0;

    public function calculate(\DateTimeImmutable $entry, \DateTimeImmutable $exit): float
    {
        $diff = $entry->diff($exit);
        $hours = $this->calculateHours($diff);
        return $hours * self::HOUR_VALUE;
    }

    private function calculateHours(\DateInterval $interval): int
    {
        $totalMin = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        return (int) ceil($totalMin / 60);
    }
}
