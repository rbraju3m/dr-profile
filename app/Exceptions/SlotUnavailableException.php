<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public static function taken(): self
    {
        return new self(__('site.booking.slot_taken'));
    }

    public static function outsideWindow(int $days): self
    {
        return new self(__('site.booking.outside_window', ['days' => bn_digits($days)]));
    }

    public static function tooManyOpen(int $count): self
    {
        return new self(__('site.booking.too_many_open', ['count' => bn_digits($count)]));
    }
}
