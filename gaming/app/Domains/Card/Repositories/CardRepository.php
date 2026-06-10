<?php

namespace App\Domains\Card\Repositories;

use App\Domains\Card\Models\Card;
use Illuminate\Support\Collection;

class CardRepository implements CardRepositoryInterface
{
    public function getBySuit(string $suit): Collection
    {
        return Card::where('suit', $suit)->orderBy('value')->get();
    }

    public function findBySuitAndValues(string $suit, array $values): Collection
    {
        return Card::where('suit', $suit)->whereIn('value', $values)->orderBy('value')->get();
    }
}
