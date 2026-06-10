<?php

namespace App\Domains\Card\Enums;

enum CardEnum: string
{
    case SPADES = 'Spades';
    case HEARTS = 'Hearts';
    case DIAMONDS = 'Diamonds';
    case CLUBS = 'Clubs';

    public static function values(): array
    {
        return array_map(fn (self $card) => $card->value, self::cases());
    }
}
