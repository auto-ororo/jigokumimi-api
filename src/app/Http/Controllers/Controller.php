<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $DATA_LIMIT = 25;
    protected $HTTP_OK = 200;
    protected $HTTP_NOT_FOUND = 404;
    protected $HTTP_INTERNAL_ERROR = 500;

    protected function responseToClient($msg, $data, $status)
    {
        $responseArray = [
            'message' => $msg,
            'data' => $data
        ];

        return response($responseArray, $status);
    }
}
