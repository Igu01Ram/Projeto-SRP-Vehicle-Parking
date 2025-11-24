<?php
namespace App\Domain\Interface;

interface PricingRule
{
    public function calculate(\DateTimeImmutable $entry, \DateTimeImmutable $exit): float;
}