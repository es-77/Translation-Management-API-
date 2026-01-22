<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for searching translations.
 */
class SearchTranslationRequest extends FormRequest
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
            'content' => [
                'sometimes',
                'string',
                'max:500',
            ],
            'tags' => [
                'sometimes',
                'array',
            ],
            'tags.*' => [
                'integer',
                'exists:tags,id',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Get the validated search filters.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->only(['key', 'locale', 'content', 'tags']);
    }

    /**
     * Get the number of items per page.
     */
    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}
