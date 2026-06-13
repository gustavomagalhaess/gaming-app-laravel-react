<?php

namespace App\Domains\Score\Repositories;

use App\Domains\Score\Models\Score;
use Illuminate\Support\Collection;

class ScoreRepositry implements ScoreRepositoryInterface
{
    /**
     * Store a new score in the database.
     */
    public function create(array $data): Score
    {
        return Score::create($data);
    }

    /**
     * Get the top 10 scores ordered by player score and creation date.
     */
    public function getTop10(): Collection
    {
        return Score::orderByDesc('player_score')
            ->orderBy('created_at')
            ->limit(10)
            ->get(['id', 'winner', 'player_name', 'player_score', 'computer_score', 'created_at'])
            ->values()
            ->map(fn ($score, $index) => [
                'rank' => $index + 1,
                'winner' => $score->winner,
                'player_score' => $score->player_score,
                'computer_score' => $score->computer_score,
                'played_at' => $score->created_at,
            ]);
    }
}
