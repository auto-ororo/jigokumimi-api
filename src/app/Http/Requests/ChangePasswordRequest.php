<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => 'required|string|min:8|max:255',
            'new_password' => 'required|string|min:8|max:255|confirmed',
            'new_password_confirmation' => 'required|string|min:8|max:255',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'message' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }

    public function withValidator(Validator $validator)
    {
    }
}