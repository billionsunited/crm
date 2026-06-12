<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorPoRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'po_number' => ['required', 'string', 'max:255'],
            'po_date' => ['required', 'date'],
            'email_id' => ['required', 'email', 'max:255'],
            'data_category' => ['required', 'array', 'min:1'],
            'data_category_other_text' => ['nullable', 'string', 'max:255'],
            'geography_filter' => ['required', 'array', 'min:1'],
            'geography_filter_other_text' => ['nullable', 'string', 'max:255'],
            'volume_records' => ['required', 'integer', 'min:1'],
            'excluded_amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
