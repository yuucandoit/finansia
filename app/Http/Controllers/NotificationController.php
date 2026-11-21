<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function feed(NotificationService $service): JsonResponse
    {
        $items = $service->getUserNotifications(Auth::user());

        return response()->json([
            'status' => 'success',
            'count'  => count($items),
            'items'  => $items,
        ]);
    }
}
