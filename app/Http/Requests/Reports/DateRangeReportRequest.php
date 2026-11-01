<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class DateRangeReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Accessible strictly to Admin and Counter Staff.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, ['admin', 'counter']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'report_type' => ['nullable', 'in:daily,monthly,custom'],
            'date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'End date cannot be prior to start date.',
            'month.between' => 'Month must be between 1 and 12.',
        ];
    }
}
