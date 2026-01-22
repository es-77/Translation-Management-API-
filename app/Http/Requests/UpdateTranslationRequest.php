<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for updating a translation.
 */
class UpdateTranslationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'locale' => [
                'sometimes',
                'string',
                'max:10',
            ],
            'value' => [
                'sometimes',
                'string',
            ],
            'tags' => [
                'sometimes',
                'array',
            ],
            'tags.*' => [
                'integer',
                'exists:tags,id',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.max' => 'The translation key must not exceed 255 characters.',
            'locale.max' => 'The locale must not exceed 10 characters.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
}
