<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
            'from_user_id' => ['required', 'integer', 'different:to_user_id', 'min:1'],
            'to_user_id' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }
}
