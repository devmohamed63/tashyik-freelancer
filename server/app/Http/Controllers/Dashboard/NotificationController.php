<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('viewAny', Notification::class);

        $notifications = Notification::forAdminOnly()->latest()->paginate(20);

        return view('dashboard.notifications.index', compact('notifications'));
    }
}
