<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchHomesRequest extends FormRequest
{
    // Who can use this request? Search is public, so everyone.
    public function authorize(): bool
    {
        return true;
    }

    // Rules for the search filters (all optional)
    public function rules(): array
    {
        return [
            'city'         => 'nullable|string|max:100',
            'max_price'    => 'nullable|integer|min:0',
            'min_bedrooms' => 'nullable|integer|min:0',
        ];
    }
}
