<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->messages();

        // 項目毎のエラーメッセージを取得してレスポンスメッセージに設定
        $message = "";
        foreach ($errors as $key => $attributesArray) {
            foreach ($attributesArray as $key2 => $value) {
                $message = $message .$value."\n";
            }
        }

        $res = response()->json([
            'message' => $message
        ], 400);
        throw new HttpResponseException($res);
    }

    public function withValidator(Validator $validator)
    {
    }
}
