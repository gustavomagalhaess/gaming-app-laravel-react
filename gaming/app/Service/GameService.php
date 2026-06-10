<?php

namespace App\Service;

use App\Domains\Card\Services\CardService;
use App\Domains\Score\Services\ScoreService;

class GameService
{
    public function __construct(
        private readonly CardService  $cardService,
        private readonly ScoreService $scoreService,
    )
    {
    }

    public function play(array $validated): array
    {
        $suitCards = $this->cardService->getBySuit($validated['suit']);
        $playerCards = $this->cardService->findBySuitAndValues($validated['suit'], $validated['selected_values']);
        $computerHand = $this->cardService->generateComputerHand($suitCards, $validated['selected_values'], count($validated['selected_values']));
        $result = $this->cardService->resolveGame($playerCards, $computerHand);

        $this->scoreService->create(
            $result['winner'],
            $validated['player_name'],
            $result['player_score'],
            $result['computer_score'],
        );

        return [$computerHand->map(fn($c) => ['face' => $c->face, 'value' => $c->value])->values(), $result];
    }
}
