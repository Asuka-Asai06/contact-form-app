<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],

            'gender' => [
                'nullable',
                'integer',
                Rule::in([1, 2, 3]),
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'date' => [
                'nullable',
                'date',
            ],
        ];
    }
}
