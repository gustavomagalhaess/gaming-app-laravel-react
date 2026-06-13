<?php

namespace App\Http\Controllers;

use App\Service\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private readonly GameService $gameService,
    ) {}

    public function play(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:100',
            'suit' => 'required|string|in:Spades,Hearts,Diamonds,Clubs',
            'selected_values' => 'required|array|min:1',
            'selected_values.*' => 'required|integer|min:1|max:13|distinct',
        ]);

        [$computerHand, $result] = $this->gameService->play($validated);

        return response()->json([
            'computer_cards' => $computerHand,
            'comparisons' => $result['comparisons'],
            'player_score' => $result['player_score'],
            'computer_score' => $result['computer_score'],
            'winner' => $result['winner'],
        ]);
    }
}
