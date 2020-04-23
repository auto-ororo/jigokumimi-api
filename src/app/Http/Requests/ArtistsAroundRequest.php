<?php

namespace App\Http\Requests;

class ArtistsAroundRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*.spotify_artist_id' => 'required',
            '*.user_id' => 'required',
            '*.longitude'    => 'required',
            '*.latitude' => 'required',
            '*.popularity' => 'required'
        ];
    }
}
