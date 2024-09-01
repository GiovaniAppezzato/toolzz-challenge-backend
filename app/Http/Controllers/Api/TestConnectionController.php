<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class TestConnectionController extends Controller
{
    public function __invoke()
    {
        return response()->json(['message' => 'Connected with success!'], 200);
    }
}
