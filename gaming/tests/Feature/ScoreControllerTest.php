<?php

namespace Tests\Feature;

use App\Domains\Score\Models\Score;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/scores ────────────────────────────────────────────────────

    public function test_store_creates_score_and_returns_201(): void
    {
        $response = $this->postJson('/api/scores', [
            'winner'         => 'Alice',
            'player_name'    => 'Alice',
            'player_score'   => 7,
            'computer_score' => 3,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['winner' => 'Alice', 'player_score' => 7]);

        $this->assertDatabaseHas('scores', ['winner' => 'Alice', 'player_score' => 7]);
    }

    public function test_store_with_computer_winner(): void
    {
        $response = $this->postJson('/api/scores', [
            'winner'         => 'Computer',
            'player_name'    => 'Bob',
            'player_score'   => 2,
            'computer_score' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('scores', ['winner' => 'Computer', 'player_name' => 'Bob']);
    }

    public function test_store_requires_winner(): void
    {
        $this->postJson('/api/scores', [
            'player_name'    => 'Alice',
            'player_score'   => 5,
            'computer_score' => 3,
        ])->assertStatus(422)->assertJsonValidationErrors('winner');
    }

    public function test_store_requires_player_name(): void
    {
        $this->postJson('/api/scores', [
            'winner'         => 'Alice',
            'player_score'   => 5,
            'computer_score' => 3,
        ])->assertStatus(422)->assertJsonValidationErrors('player_name');
    }

    public function test_store_requires_player_score(): void
    {
        $this->postJson('/api/scores', [
            'winner'         => 'Alice',
            'player_name'    => 'Alice',
            'computer_score' => 3,
        ])->assertStatus(422)->assertJsonValidationErrors('player_score');
    }

    public function test_store_requires_computer_score(): void
    {
        $this->postJson('/api/scores', [
            'winner'       => 'Alice',
            'player_name'  => 'Alice',
            'player_score' => 5,
        ])->assertStatus(422)->assertJsonValidationErrors('computer_score');
    }

    public function test_store_rejects_winner_exceeding_100_chars(): void
    {
        $this->postJson('/api/scores', [
            'winner'         => str_repeat('a', 101),
            'player_name'    => 'Alice',
            'player_score'   => 5,
            'computer_score' => 3,
        ])->assertStatus(422)->assertJsonValidationErrors('winner');
    }

    public function test_store_rejects_negative_score(): void
    {
        $this->postJson('/api/scores', [
            'winner'         => 'Alice',
            'player_name'    => 'Alice',
            'player_score'   => -1,
            'computer_score' => 3,
        ])->assertStatus(422)->assertJsonValidationErrors('player_score');
    }

    // ── GET /api/scores/top10 ───────────────────────────────────────────────

    public function test_top10_returns_empty_data_when_no_scores(): void
    {
        $response = $this->getJson('/api/scores/top10');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    public function test_top10_returns_up_to_10_records(): void
    {
        Score::factory()->count(15)->create();

        $response = $this->getJson('/api/scores/top10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
    }

    public function test_top10_ordered_by_player_score_descending(): void
    {
        Score::factory()->create(['player_score' => 3, 'winner' => 'Low']);
        Score::factory()->create(['player_score' => 9, 'winner' => 'High']);
        Score::factory()->create(['player_score' => 6, 'winner' => 'Mid']);

        $data = $this->getJson('/api/scores/top10')->json('data');

        $this->assertSame('High', $data[0]['winner']);
        $this->assertSame('Mid',  $data[1]['winner']);
        $this->assertSame('Low',  $data[2]['winner']);
    }

    public function test_top10_response_has_correct_shape(): void
    {
        Score::factory()->create([
            'winner'         => 'Alice',
            'player_score'   => 8,
            'computer_score' => 2,
        ]);

        $data = $this->getJson('/api/scores/top10')->json('data');

        $this->assertArrayHasKey('rank', $data[0]);
        $this->assertArrayHasKey('winner', $data[0]);
        $this->assertArrayHasKey('player_score', $data[0]);
        $this->assertArrayHasKey('computer_score', $data[0]);
        $this->assertArrayHasKey('played_at', $data[0]);
        $this->assertSame(1, $data[0]['rank']);
    }

    public function test_top10_rank_starts_at_1(): void
    {
        Score::factory()->count(3)->create();

        $data = $this->getJson('/api/scores/top10')->json('data');

        $ranks = array_column($data, 'rank');
        $this->assertSame([1, 2, 3], $ranks);
    }
}
