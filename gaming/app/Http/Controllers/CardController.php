<?php

namespace App\Http\Controllers;

use App\Repositories\CardRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    private const VALID_SUITS = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];

    public function __construct(private CardRepositoryInterface $cards) {}

    public function index(string $suit): JsonResponse
    {
        if (!in_array($suit, self::VALID_SUITS, true)) {
            return response()->json([
                'message' => 'The suit field must be one of: Spades, Hearts, Diamonds, Clubs.',
                'errors'  => ['suit' => ['Invalid suit: ' . $suit]],
            ], 422);
        }

        $cards = $this->cards->getBySuit($suit)->map(fn ($card) => [
            'id'    => $card->id,
            'suit'  => $card->suit,
            'face'  => $card->face,
            'value' => $card->value,
        ])->values();

        return response()->json(['data' => $cards]);
    }
}
