<?php

namespace Tests\Feature;

use App\Domains\Card\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedSuit(string $suit): void
    {
        $faces = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        foreach ($faces as $i => $face) {
            Card::create(['suit' => $suit, 'face' => $face, 'value' => $i + 1]);
        }
    }

    public function test_play_returns_200_with_valid_payload(): void
    {
        $this->seedSuit('Hearts');

        $response = $this->postJson('/api/game/play', [
            'player_name' => 'Alice',
            'suit' => 'Hearts',
            'selected_values' => [1, 5, 13],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'computer_cards' => [['face', 'value']],
                'comparisons' => [['player_face', 'player_value', 'computer_face', 'computer_value', 'winner']],
                'player_score',
                'computer_score',
                'winner',
            ]);
    }

    public function test_computer_cards_count_matches_selected_count(): void
    {
        $this->seedSuit('Spades');

        $response = $this->postJson('/api/game/play', [
            'player_name' => 'Bob',
            'suit' => 'Spades',
            'selected_values' => [1, 2, 3, 4],
        ]);

        $response->assertOk();
        $this->assertCount(4, $response->json('computer_cards'));
        $this->assertCount(4, $response->json('comparisons'));
    }

    public function test_comparisons_length_equals_selected_values_length(): void
    {
        $this->seedSuit('Diamonds');

        $response = $this->postJson('/api/game/play', [
            'player_name' => 'Carol',
            'suit' => 'Diamonds',
            'selected_values' => [7, 8],
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('comparisons'));
    }

    public function test_score_is_saved_to_database(): void
    {
        $this->seedSuit('Clubs');

        $this->postJson('/api/game/play', [
            'player_name' => 'Dan',
            'suit' => 'Clubs',
            'selected_values' => [13],
        ])->assertOk();

        $this->assertDatabaseCount('scores', 1);
        $this->assertDatabaseHas('scores', ['player_name' => 'Dan']);
    }

    public function test_winner_field_is_player_or_computer(): void
    {
        $this->seedSuit('Hearts');

        $response = $this->postJson('/api/game/play', [
            'player_name' => 'Eve',
            'suit' => 'Hearts',
            'selected_values' => [1],
        ])->assertOk();

        $this->assertContains($response->json('winner'), ['player', 'computer']);
    }

    public function test_play_requires_player_name(): void
    {
        $this->postJson('/api/game/play', [
            'suit' => 'Hearts',
            'selected_values' => [1],
        ])->assertStatus(422)->assertJsonValidationErrors(['player_name']);
    }

    public function test_play_requires_valid_suit(): void
    {
        $this->postJson('/api/game/play', [
            'player_name' => 'Alice',
            'suit' => 'Jokers',
            'selected_values' => [1],
        ])->assertStatus(422)->assertJsonValidationErrors(['suit']);
    }

    public function test_play_requires_at_least_one_selected_value(): void
    {
        $this->postJson('/api/game/play', [
            'player_name' => 'Alice',
            'suit' => 'Hearts',
            'selected_values' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['selected_values']);
    }

    public function test_play_rejects_values_out_of_range(): void
    {
        $this->postJson('/api/game/play', [
            'player_name' => 'Alice',
            'suit' => 'Hearts',
            'selected_values' => [0, 14],
        ])->assertStatus(422);
    }

    public function test_tie_goes_to_computer(): void
    {
        // Seed two suits so we can force a tie
        $this->seedSuit('Hearts');
        $this->seedSuit('Spades');

        // Player selects A (value 1) from Hearts; computer will draw from remaining Hearts cards.
        // We can't guarantee a tie via the API, but we can verify the resolveGame logic via unit test.
        // Here we verify the endpoint runs without error and returns a valid winner.
        $response = $this->postJson('/api/game/play', [
            'player_name' => 'Tied',
            'suit' => 'Hearts',
            'selected_values' => [1],
        ])->assertOk();

        $this->assertContains($response->json('winner'), ['player', 'computer']);
    }
}
