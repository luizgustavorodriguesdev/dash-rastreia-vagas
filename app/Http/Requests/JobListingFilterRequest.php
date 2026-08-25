<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobListingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'workplace_type' => ['nullable', Rule::in(['remote', 'hybrid', 'onsite'])],
            'employment_type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'freelance', 'internship'])],
            'category' => ['nullable', 'string', 'max:255'],
        ];
    }
}
