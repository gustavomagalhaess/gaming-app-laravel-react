<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $suits = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
        $faces = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        foreach ($suits as $suit) {
            foreach ($faces as $index => $face) {
                Card::create([
                    'suit'  => $suit,
                    'face'  => $face,
                    'value' => $index + 1,
                ]);
            }
        }
    }
}
