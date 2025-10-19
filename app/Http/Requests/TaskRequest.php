<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'description' => 'required',
            'due_date' => 'nullable|date|after_or_equal:today'
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Description is required.',
            'due_date.date' => 'Due Date must be a valid date.',
            'due_date.after_or_equal' => 'Please select today or a future date.',
        ];
    }
}
