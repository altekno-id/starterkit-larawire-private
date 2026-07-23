<?php

namespace App\Http\Controllers\Starter\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TouchSessionActivityController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        if ((bool) $request->session()->get('starter.locked', false)) {
            return response()->json([
                'redirect' => route('starter.lock-screen'),
            ], 423);
        }

        $request->session()->put('starter.last_activity_at', now()->timestamp);

        return response()->noContent();
    }
}
