<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Repositories\CardRepositoryInterface;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private CardRepositoryInterface $cards,
        private CardService $cardService,
    ) {}

    public function play(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_name'       => 'required|string|max:100',
            'suit'              => 'required|string|in:Spades,Hearts,Diamonds,Clubs',
            'selected_values'   => 'required|array|min:1',
            'selected_values.*' => 'required|integer|min:1|max:13|distinct',
        ]);

        $suitCards    = $this->cards->getBySuit($validated['suit']);
        $playerCards  = $this->cards->findBySuitAndValues($validated['suit'], $validated['selected_values']);
        $computerHand = $this->cardService->generateComputerHand($suitCards, $validated['selected_values'], count($validated['selected_values']));
        $result       = $this->cardService->resolveGame($playerCards, $computerHand);

        $winnerName = $result['winner'] === 'player' ? $validated['player_name'] : 'Computer';

        Score::create([
            'winner'         => $winnerName,
            'player_name'    => $validated['player_name'],
            'player_score'   => $result['player_score'],
            'computer_score' => $result['computer_score'],
        ]);

        return response()->json([
            'computer_cards' => $computerHand->map(fn ($c) => ['face' => $c->face, 'value' => $c->value])->values(),
            'comparisons'    => $result['comparisons'],
            'player_score'   => $result['player_score'],
            'computer_score' => $result['computer_score'],
            'winner'         => $result['winner'],
        ]);
    }
}
