<?php

namespace App\Http\Controllers\Score;

use App\Domains\Score\Messages\ScoreMessage;
use App\Domains\Score\Repositories\ScoreRepositoryInterface;
use App\Domains\Score\Requests\ScoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ScoreController extends Controller
{
    public function __construct(
        private readonly ScoreRepositoryInterface $scoreRepository
    )
    {
    }

    public function store(ScoreRequest $request): JsonResponse
    {
        try {
            $score = $this->scoreRepository->create($request->validated());
        } catch (\Exception $e) {
            return response()->json(['error' => ScoreMessage::FAILED_TO_CREATE_ERROR, 'message' => $e->getMessage()], 500);
        }

        return response()->json($score, 201);
    }

    public function top10(): JsonResponse
    {
        $scores = $this->scoreRepository->getTop10();

        return response()->json(['data' => $scores]);
    }
}
