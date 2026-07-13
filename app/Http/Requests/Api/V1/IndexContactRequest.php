<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexContactRequest extends FormRequest
{
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
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.in' => '性別値が不正です',
            'gender.integer' => '性別の形式が不正です',
            'category_id.exists' => '選択されたカテゴリーが存在しません',
        ];
    }
}
