<?php

namespace Tests\Feature;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedHearts(): void
    {
        $faces = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        foreach ($faces as $i => $face) {
            Card::create(['suit' => 'Hearts', 'face' => $face, 'value' => $i + 1]);
        }
    }

    public function test_returns_13_cards_for_valid_suit(): void
    {
        $this->seedHearts();

        $response = $this->getJson('/api/cards/Hearts');

        $response->assertOk()
            ->assertJsonCount(13, 'data')
            ->assertJsonPath('data.0.face', 'A')
            ->assertJsonPath('data.0.value', 1)
            ->assertJsonPath('data.12.face', 'K')
            ->assertJsonPath('data.12.value', 13);
    }

    public function test_cards_are_ordered_by_value(): void
    {
        $this->seedHearts();

        $data = $this->getJson('/api/cards/Hearts')->json('data');

        $values = array_column($data, 'value');
        $this->assertEquals(range(1, 13), $values);
    }

    public function test_response_includes_expected_fields(): void
    {
        $this->seedHearts();

        $data = $this->getJson('/api/cards/Hearts')->json('data.0');

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('suit', $data);
        $this->assertArrayHasKey('face', $data);
        $this->assertArrayHasKey('value', $data);
        $this->assertSame('Hearts', $data['suit']);
    }

    public function test_returns_422_for_invalid_suit(): void
    {
        $this->getJson('/api/cards/Jokers')->assertStatus(422);
    }

    public function test_returns_422_for_lowercase_suit(): void
    {
        $this->getJson('/api/cards/hearts')->assertStatus(422);
    }
}
