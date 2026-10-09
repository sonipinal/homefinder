<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHomeRequest extends FormRequest
{
        public function authorize(): bool
        {
            return true; // later (Day 5) only admins will be allowed
        }

        public function rules(): array
        {
            return [
                'community_id' => 'required|integer|exists:communities,id',
                'address'      => 'required|string|max:255',
                'price'        => 'required|integer|min:0',
                'bedrooms'     => 'required|integer|min:0|max:20',
            ];
        }

        // Friendlier error messages
        public function messages(): array
        {
            return [
                'community_id.exists' => 'That community does not exist.',
                'price.integer'       => 'Price must be a whole number, like 350000.',
            ];
        }
    }
