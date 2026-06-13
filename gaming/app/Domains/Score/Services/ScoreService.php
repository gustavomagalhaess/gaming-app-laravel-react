<?php

namespace App\Domains\Score\Services;

use App\Domains\Score\Models\Score;
use App\Domains\Score\Repositories\ScoreRepositoryInterface;

class ScoreService
{
    public function __construct(
        private ScoreRepositoryInterface $scoreRepository
    ) {}

    public function create(
        string $winner,
        string $playerName,
        string $playerScore,
        string $computerScore,
    ): Score {

        $winnerName = $winner === 'player' ? $playerName : 'Computer';

        return $this->scoreRepository->create([
            'winner' => $winnerName,
            'player_name' => $playerName,
            'player_score' => $playerScore,
            'computer_score' => $computerScore,
        ]);
    }
}
