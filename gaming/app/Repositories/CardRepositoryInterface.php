<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface CardRepositoryInterface
{
    public function getBySuit(string $suit): Collection;

    public function findBySuitAndValues(string $suit, array $values): Collection;
}
