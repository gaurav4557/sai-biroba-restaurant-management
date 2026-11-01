<?php

namespace App\Http\Requests\Waiter;

use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;

class StartVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'waiter';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $table = $this->route('table');
        $maxCapacity = ($table instanceof RestaurantTable) ? $table->seating_capacity : 50;

        return [
            'guest_count' => ['required', 'integer', 'min:1', 'max:' . $maxCapacity],
            'guest_name'  => ['nullable', 'string', 'max:100'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guest_count.required' => 'Guest count is required to seat guests.',
            'guest_count.integer'  => 'Guest count must be a whole number.',
            'guest_count.min'      => 'At least 1 guest must be seated.',
            'guest_count.max'      => 'Guest count exceeds table seating capacity (:max).',
            'guest_name.max'       => 'Guest name cannot exceed 100 characters.',
            'notes.max'            => 'Notes cannot exceed 255 characters.',
        ];
    }
}
