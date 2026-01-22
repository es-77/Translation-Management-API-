<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for storing a new translation.
 */
class StoreTranslationRequest extends FormRequest
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
                'required',
                'string',
                'max:255',
            ],
            'locale' => [
                'required',
                'string',
                'max:10',
            ],
            'value' => [
                'required',
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
            'key.required' => 'The translation key is required.',
            'key.max' => 'The translation key must not exceed 255 characters.',
            'locale.required' => 'The locale is required.',
            'locale.max' => 'The locale must not exceed 10 characters.',
            'value.required' => 'The translation value is required.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
}
