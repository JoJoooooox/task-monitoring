<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// In your routes/web.php
Route::post('/daily/host-token', function(Request $request) {
    try {
        $validated = $request->validate([
            'room_name' => 'required|string'
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.daily.key'),
            'Content-Type' => 'application/json'
        ])->post('https://api.daily.co/v1/meeting-tokens', [
            'properties' => [
                'room_name' => $validated['room_name'],
                'exp' => now()->addMinutes(45)->timestamp, // Max 60 mins for free
                'enable_recording' => 'off', // Recording requires payment
                'enable_prejoin_ui' => true,
                'start_video_off' => true,
                'enable_screenshare' => true,
                'enable_chat' => true,
                'enable_knocking' => true,
            ]
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Daily.co API Error: ' . $response->body()
            ], 500);
        }

        return $response->json();

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/daily/end-meeting', function(Request $request) {
    $validated = $request->validate([
        'room_name' => 'required|string'
    ]);

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.daily.key'),
    ])->delete("https://api.daily.co/v1/rooms/" . $validated['room_name']);

    return $response->successful()
        ? response()->json(['success' => true])
        : response()->json(['error' => 'Failed to delete room'], 500);
});