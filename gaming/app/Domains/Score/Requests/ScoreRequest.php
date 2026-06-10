<?php

namespace App\Domains\Score\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'winner' => 'required|string|max:100',
            'player_name' => 'required|string|max:100',
            'player_score' => 'required|integer|min:0',
            'computer_score' => 'required|integer|min:0',
        ];
    }
}
