<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;

class NotificationController extends ApiController
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->orderByDesc('id')
            ->paginate($this->paginationLimit, [
                'id',
                'type',
                'title',
                'description',
                'data',
                'created_at',
            ]);

        return NotificationResource::collection($notifications);
    }
}
