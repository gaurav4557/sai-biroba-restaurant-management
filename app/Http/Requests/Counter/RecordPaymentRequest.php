<?php

namespace App\Http\Requests\Counter;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, ['counter', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $method = $this->input('payment_method');

        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,credit_card,debit_card,other'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'confirm_cash' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];

        // Conditional validation based on payment method
        if ($method === 'cash') {
            $rules['confirm_cash'] = ['required', 'accepted'];
        } elseif (in_array($method, ['upi', 'credit_card', 'debit_card'])) {
            $rules['transaction_ref'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least ₹0.01.',
            'payment_method.required' => 'Please select a valid payment method.',
            'confirm_cash.accepted' => 'You must confirm physical receipt of cash before submitting.',
            'transaction_ref.required' => 'Transaction reference / Auth Code is required for digital payments.',
        ];
    }
}
