<?php

namespace App\Http\Controllers;

class HealthCheckController extends Controller
{
    /**
     * 疎通確認
     *
     */
    public function index()
    {
        return response("OK", 200);
    }
}
