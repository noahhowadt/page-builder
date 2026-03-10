<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('slug')) {
            return;
        }
        $slug = trim((string) $this->input('slug', ''));
        if ($slug === '') {
            $slug = '/';
        } elseif (! str_starts_with($slug, '/')) {
            $slug = '/'.$slug;
        }
        $this->merge(['slug' => $slug]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^\/([a-zA-Z0-9_-]+(\/[a-zA-Z0-9_-]+)*)?$/',
                Rule::unique('pages', 'slug')->ignore($page),
            ],
            'is_published' => ['sometimes', 'boolean'],
            'structure' => ['sometimes', 'required', 'array'],
        ];
    }
}
