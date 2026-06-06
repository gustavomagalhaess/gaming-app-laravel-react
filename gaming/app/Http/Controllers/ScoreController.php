<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'winner'         => 'required|string|max:100',
            'player_name'    => 'required|string|max:100',
            'player_score'   => 'required|integer|min:0',
            'computer_score' => 'required|integer|min:0',
        ]);

        $score = Score::create($validated);

        return response()->json($score, 201);
    }

    public function top10(): JsonResponse
    {
        $scores = Score::orderByDesc('player_score')
            ->orderBy('created_at')
            ->limit(10)
            ->get(['id', 'winner', 'player_name', 'player_score', 'computer_score', 'created_at'])
            ->values()
            ->map(fn ($score, $index) => [
                'rank'           => $index + 1,
                'winner'         => $score->winner,
                'player_score'   => $score->player_score,
                'computer_score' => $score->computer_score,
                'played_at'      => $score->created_at,
            ]);

        return response()->json(['data' => $scores]);
    }
}
