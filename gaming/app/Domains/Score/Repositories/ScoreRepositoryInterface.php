<?php

namespace App\Domains\Score\Repositories;

use App\Domains\Score\Models\Score;
use Illuminate\Support\Collection;

interface ScoreRepositoryInterface
{
    /**
     * Store a new score in the database.
     */
    public function create(array $data): Score;

    /**
     * Get the top 10 scores ordered by player score and creation date.
     */
    public function getTop10(): Collection;
}
