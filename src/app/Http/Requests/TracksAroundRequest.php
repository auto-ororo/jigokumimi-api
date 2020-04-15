<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TracksAroundRequest extends FormRequest
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
            '*.spotify_track_id' => 'required',
            '*.user_id' => 'required',
            '*.longitude'    => 'required',
            '*.latitude' => 'required',
            '*.popularity' => 'required'
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
