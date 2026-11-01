<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignWaiterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
            'waiter_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'table_id' => 'dining table',
            'waiter_id' => 'waiter staff',
            'notes' => 'assignment notes',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'table_id.required' => 'Please select a dining table to assign.',
            'table_id.exists' => 'The selected dining table does not exist.',
            'waiter_id.required' => 'Please select an active waiter for this assignment.',
            'waiter_id.exists' => 'The selected staff member does not exist in the system.',
        ];
    }
}
