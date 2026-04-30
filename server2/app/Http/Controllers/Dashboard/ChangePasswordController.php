<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function index()
    {
        return view('dashboard.change-password');
    }

    public function update(ChangePasswordRequest $request)
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Re-authenticate to keep the session valid after password change
        Auth::login($user);

        return redirect()
            ->route('dashboard.change_password.index')
            ->with(['status' => __('ui.password_changed_successfully')]);
    }
}
