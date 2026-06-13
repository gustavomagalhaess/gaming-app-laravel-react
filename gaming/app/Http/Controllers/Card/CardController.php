<?php

namespace App\Http\Controllers\Card;

use App\Domains\Card\Messages\CardMessage;
use App\Domains\Card\Services\CardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CardController extends Controller
{
    public function __construct(
        private CardService $cardService,
    ) {}

    public function index(string $suit): JsonResponse
    {
        if ($this->cardService->validateSuit($suit)) {
            return response()->json([
                'message' => CardMessage::SUIT_VALIDATION_ERROR,
                'errors' => ['suit' => ['Invalid suit: '.$suit]],
            ], 422);
        }

        $cards = $this->cardService->getBySuitMapped($suit);

        return response()->json(['data' => $cards]);
    }
}
