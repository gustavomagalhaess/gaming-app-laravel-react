<?php

namespace Database\Factories;

use App\Domains\Score\Models\Score;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScoreFactory extends Factory
{
    protected $model = Score::class;

    public function definition(): array
    {
        $playerScore = $this->faker->numberBetween(0, 13);
        $computerScore = 13 - $playerScore;
        $playerName = $this->faker->firstName();
        $winner = $playerScore > $computerScore ? $playerName : 'Computer';

        return [
            'winner' => $winner,
            'player_name' => $playerName,
            'player_score' => $playerScore,
            'computer_score' => $computerScore,
        ];
    }
}
