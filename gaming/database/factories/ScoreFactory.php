<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScoreFactory extends Factory
{
    public function definition(): array
    {
        $playerScore   = $this->faker->numberBetween(0, 13);
        $computerScore = 13 - $playerScore;
        $playerName    = $this->faker->firstName();
        $winner        = $playerScore > $computerScore ? $playerName : 'Computer';

        return [
            'winner'         => $winner,
            'player_name'    => $playerName,
            'player_score'   => $playerScore,
            'computer_score' => $computerScore,
        ];
    }
}
