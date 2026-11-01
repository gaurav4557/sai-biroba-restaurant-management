<?php

namespace App\Http\Requests\Waiter;

use Illuminate\Foundation\Http\FormRequest;

class SubmitOrderRequest extends FormRequest
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
        return [
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.menu_item_id'        => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.quantity'            => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.special_instructions'=> ['nullable', 'string', 'max:255'],
            'order_notes'                 => ['nullable', 'string', 'max:255'],
            'idempotency_token'           => ['nullable', 'string', 'max:64'],
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
            'items.required'                => 'At least one menu item must be selected.',
            'items.min'                     => 'Please add at least one item to the order.',
            'items.*.menu_item_id.required' => 'A valid menu item is required.',
            'items.*.menu_item_id.exists'   => 'Selected menu item does not exist.',
            'items.*.quantity.required'     => 'Quantity is required for all items.',
            'items.*.quantity.min'          => 'Quantity must be at least 1.',
            'items.*.quantity.max'          => 'Quantity cannot exceed 50 per item.',
            'items.*.special_instructions.max' => 'Special instructions cannot exceed 255 characters.',
            'order_notes.max'               => 'Order notes cannot exceed 255 characters.',
        ];
    }
}
