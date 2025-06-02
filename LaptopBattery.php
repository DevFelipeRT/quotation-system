<?php

declare(strict_types=1);

class LaptopBattery
{
    private const CAPACITY = 100;
    private const MINIMUM_CHARGE = 0;

    private int $currentCharge;
    private array $events = [];

    public function __construct(int $initialCharge)
    {
        $this->currentCharge = $this->validateInitialCharge($initialCharge);
    }

    public function setEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->validateEvent($event);
            $this->events[] = $event;
        }
    }

    public function applyEvents(): void
    {
        foreach ($this->events as $amount) {
            if ($this->isCharge($amount)) {
                $this->charge($amount);
            } elseif ($this->isDischarge($amount)) {
                $this->discharge($amount);
            }
        }
    }

    public function getBattery(): int
    {
        return $this->currentCharge;
    }

    private function validateInitialCharge(int $initialCharge): int
    {
        if ($initialCharge < self::MINIMUM_CHARGE || $initialCharge > self::CAPACITY) {
            throw new \InvalidArgumentException('Initial charge must be between ' . self::MINIMUM_CHARGE . ' and ' . self::CAPACITY . '.');
        }
        return $initialCharge;
    }

    private function validateEvent($amount): void
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException('Event must be an integer.');
        }
    }

    private function isCharge(int $amount): bool
    {
        return $amount > 0;
    }

    private function isDischarge(int $amount): bool
    {
        return $amount < 0;
    }

    private function charge(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Charge amount must be positive.');
        }
        $newCharge = $this->clampCharge($this->currentCharge + $amount);
        $this->currentCharge = $newCharge;
    }

    private function discharge(int $amount): void
    {
        if ($amount > 0) {
            throw new \InvalidArgumentException('Discharge amount must be negative.');
        }
        $newCharge = $this->clampCharge($this->currentCharge + $amount);
        $this->currentCharge = $newCharge;
    }

    private function clampCharge(int $charge): int
    {
        return max(self::MINIMUM_CHARGE, min(self::CAPACITY, $charge));
    }
}

$laptopBattery = new LaptopBattery(50);
$events = [10, -20, 61, -15];
$laptopBattery->setEvents($events);
$laptopBattery->applyEvents();
$currentCharge = $laptopBattery->getBattery();
echo "Current charge after applying events: $currentCharge%";
