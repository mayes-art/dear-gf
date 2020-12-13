<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LobbyController extends Controller
{
    public function lineGet()
    {
        try {
            $event = request()->all();
            Log::info($event);
            return response('test', 200);
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function linePost(Request $request)
    {
        try {
            $event = $request->all();
            Log::info($event);
            return response('test', 200);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
