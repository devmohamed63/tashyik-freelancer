<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeEmailController extends Controller
{
    /**
     * Display the change email view.
     */
    public function index()
    {
        return view('dashboard.change-email');
    }

    /**
     * Update the user's email address.
     */
    public function update(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
        ]);

        $user = $request->user();

        $user->update([
            'email' => $request->email,
        ]);

        return redirect()
            ->route('dashboard.change_email.index')
            ->with(['status' => __('ui.email_changed_successfully')]);
    }
}
