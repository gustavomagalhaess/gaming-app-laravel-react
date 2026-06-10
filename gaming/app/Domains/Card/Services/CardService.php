<?php

namespace App\Domains\Card\Services;

use App\Domains\Card\Enums\CardEnum;
use App\Domains\Card\Models\Card;
use App\Domains\Card\Repositories\CardRepositoryInterface;
use Illuminate\Support\Collection;

class CardService
{
    public function __construct(
        private CardRepositoryInterface $cardRepository,
    )
    {
    }

    public function validateSuit(string $suit): bool
    {
        return !in_array($suit, CardEnum::values(), true);
    }

    public function getBySuit(string $suit): Collection
    {
        return $this->cardRepository->getBySuit($suit);
    }

    public function getBySuitMapped(string $suit): Collection
    {
        return $this->getBySuit($suit)->map(fn($card) => [
            'id' => $card->id,
            'suit' => $card->suit,
            'face' => $card->face,
            'value' => $card->value,
        ])->values();
    }

    public function findBySuitAndValues(string $suit, array $values): Collection
    {
        return $this->cardRepository->findBySuitAndValues($suit, $values);
    }

    /**
     * Draw $count cards from $suitCards excluding any with values in $playerValues.
     * Uses Fisher-Yates shuffle for uniform random distribution.
     */
    public function generateComputerHand(Collection $suitCards, array $playerValues, int $count): Collection
    {
        $pool = $suitCards->filter(fn (Card $card) => ! in_array($card->value, $playerValues, true))->values();

        // Fisher-Yates shuffle
        $arr = $pool->all();
        for ($i = count($arr) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }

        return collect(array_slice($arr, 0, $count));
    }

    /**
     * Compare each player card (by value) against the corresponding computer card.
     * Ties go to the computer.
     */
    public function resolveGame(Collection $playerCards, Collection $computerCards): array
    {
        $comparisons = [];

        foreach ($playerCards as $i => $playerCard) {
            $computerCard = $computerCards->get($i);

            $playerValue = $playerCard->value;
            $computerValue = $computerCard?->value ?? -INF;

            $winner = $playerValue > $computerValue ? 'player' : 'computer';

            $comparisons[] = [
                'player_face' => $playerCard->face,
                'player_value' => $playerValue,
                'computer_face' => $computerCard?->face,
                'computer_value' => $computerCard?->value,
                'winner' => $winner,
            ];
        }

        $playerScore = count(array_filter($comparisons, fn($c) => $c['winner'] === 'player'));
        $computerScore = count(array_filter($comparisons, fn($c) => $c['winner'] === 'computer'));

        return [
            'comparisons' => $comparisons,
            'player_score' => $playerScore,
            'computer_score' => $computerScore,
            'winner' => $playerScore > $computerScore ? 'player' : 'computer',
        ];
    }
}
