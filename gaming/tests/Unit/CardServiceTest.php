<?php

namespace Tests\Unit;

use App\Domains\Card\Models\Card;
use App\Domains\Card\Services\CardService;
use Tests\TestCase;

class CardServiceTest extends TestCase
{
    private CardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CardService::class);
    }

    private function makeCard(string $face, int $value, string $suit = 'Hearts'): Card
    {
        $card = app(Card::class);
        $card->face = $face;
        $card->value = $value;
        $card->suit = $suit;

        return $card;
    }

    public function test_resolve_game_player_wins_higher_value(): void
    {
        $playerCards = collect([$this->makeCard('K', 13)]);
        $computerCards = collect([$this->makeCard('2', 2)]);

        $result = $this->service->resolveGame($playerCards, $computerCards);

        $this->assertSame('player', $result['winner']);
        $this->assertSame(1, $result['player_score']);
        $this->assertSame(0, $result['computer_score']);
    }

    public function test_resolve_game_tie_goes_to_computer(): void
    {
        $playerCards = collect([$this->makeCard('5', 5)]);
        $computerCards = collect([$this->makeCard('5', 5)]);

        $result = $this->service->resolveGame($playerCards, $computerCards);

        $this->assertSame('computer', $result['winner']);
        $this->assertSame(0, $result['player_score']);
        $this->assertSame(1, $result['computer_score']);
    }

    public function test_resolve_game_computer_wins_higher_value(): void
    {
        $playerCards = collect([$this->makeCard('A', 1)]);
        $computerCards = collect([$this->makeCard('K', 13)]);

        $result = $this->service->resolveGame($playerCards, $computerCards);

        $this->assertSame('computer', $result['winner']);
    }

    public function test_resolve_game_multiple_cards(): void
    {
        $playerCards = collect([
            $this->makeCard('K', 13),
            $this->makeCard('Q', 12),
            $this->makeCard('A', 1),
        ]);
        $computerCards = collect([
            $this->makeCard('2', 2),
            $this->makeCard('J', 11),
            $this->makeCard('5', 5),
        ]);

        $result = $this->service->resolveGame($playerCards, $computerCards);

        // K(13) > 2(2) → player, Q(12) > J(11) → player, A(1) < 5(5) → computer
        $this->assertSame(2, $result['player_score']);
        $this->assertSame(1, $result['computer_score']);
        $this->assertSame('player', $result['winner']);
    }

    public function test_resolve_game_comparisons_contain_correct_faces(): void
    {
        $playerCards = collect([$this->makeCard('K', 13)]);
        $computerCards = collect([$this->makeCard('A', 1)]);

        $result = $this->service->resolveGame($playerCards, $computerCards);

        $this->assertSame('K', $result['comparisons'][0]['player_face']);
        $this->assertSame('A', $result['comparisons'][0]['computer_face']);
    }

    public function test_generate_computer_hand_excludes_player_values(): void
    {
        $suitCards = collect([
            $this->makeCard('A', 1),
            $this->makeCard('2', 2),
            $this->makeCard('3', 3),
            $this->makeCard('K', 13),
        ]);

        $hand = $this->service->generateComputerHand($suitCards, [1, 13], 2);

        $values = $hand->pluck('value')->all();
        $this->assertNotContains(1, $values);
        $this->assertNotContains(13, $values);
        $this->assertCount(2, $hand);
    }

    public function test_generate_computer_hand_count_matches_requested(): void
    {
        $faces = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $suitCards = collect(array_map(fn ($i) => $this->makeCard($faces[$i], $i + 1), range(0, 12)));

        $hand = $this->service->generateComputerHand($suitCards, [1], 5);

        $this->assertCount(5, $hand);
    }
}
