<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordRequest;
use App\Http\Requests\Auth\ProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function update(ProfileRequest $request)
    {
        /**
         * @var User
         */
        $user = Auth::user();

        $user->update($request->validated());

        if ($request->image) {
            $user->addMediaFromRequest('image')
                ->toMediaCollection('avatar');
        }

        if ($request->national_address_image) {
            $user->addMediaFromRequest('national_address_image')
                ->toMediaCollection('national_address_image');
        }

        return new UserResource($user->load('institution'));
    }

    public function update_password(PasswordRequest $request)
    {
        /**
         * @var User
         */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response(null);
    }

    public function delete()
    {
        /**
         * @var User
         */
        $user = Auth::user();

        // Members cannot delete their own account — admin manages them
        abort_if($user->institution_id, 403);

        Auth::guard('web')->logout();

        $user->forceDelete();

        return response(null);
    }
}
