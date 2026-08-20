<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * What a patient can book at one chamber on one date.
 */
final class DayAvailability
{
    /**
     * @param  array<int, array{time: string, label: string, schedule_id: int|null, taken: bool}>  $slots
     */
    public function __construct(
        public readonly Carbon $date,
        public readonly bool $isOpen,
        public readonly array $slots = [],
        public readonly ?string $closedReason = null,
    ) {}

    public static function closed(Carbon $date, ?string $reason = null): self
    {
        return new self($date, false, [], $reason);
    }

    /** @return array<int, array{time: string, label: string, schedule_id: int|null, taken: bool}> */
    public function openSlots(): array
    {
        return array_values(array_filter($this->slots, fn (array $slot) => ! $slot['taken']));
    }

    public function openCount(): int
    {
        return count($this->openSlots());
    }

    public function hasOpenSlots(): bool
    {
        return $this->openCount() > 0;
    }

    /** The schedule a given slot belongs to, or null when the slot is not offered. */
    public function scheduleIdFor(string $time): ?int
    {
        foreach ($this->slots as $slot) {
            if ($slot['time'] === $time && ! $slot['taken']) {
                return $slot['schedule_id'];
            }
        }

        return null;
    }

    public function offers(string $time): bool
    {
        foreach ($this->slots as $slot) {
            if ($slot['time'] === $time && ! $slot['taken']) {
                return true;
            }
        }

        return false;
    }
}
