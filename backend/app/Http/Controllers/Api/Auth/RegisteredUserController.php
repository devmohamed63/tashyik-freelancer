<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\NewUser;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        // Basic information
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:10', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'city' => ['required', 'integer', 'exists:cities,id'],
            'account_type' => ['required', Rule::in(User::AVAILABLE_ACCOUNT_TYPES)],
        ];

        // Shared information unless type is user
        if ($request->account_type != User::USER_ACCOUNT_TYPE) {
            $rules = array_merge($rules, [
                'categories' => ['required', 'array', 'min:1', 'max:2'],
                'categories.*' => ['nullable', 'integer', 'exists:categories,id'],
                'entity_type' => ['required', Rule::in(User::AVAILABLE_ENTITY_TYPES)],
                'residence_name' => ['required', 'string', 'max:255'],
                'residence_number' => ['required', 'string', 'max:255'],
                'residence_image' => ['required', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
                'bank_name' => ['required', 'string', 'max:255'],
                'iban' => ['required', 'string', 'max:255'],
            ]);
        }

        switch ($request->entity_type) {
            case User::INDIVIDUAL_ENTITY_TYPE:
                $rules = array_merge($rules, [
                    'personal_picture' => ['required', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
                ]);
                break;

            case User::INSTITUTION_ENTITY_TYPE:
            case User::COMPANY_ENTITY_TYPE:
                $rules = array_merge($rules, [
                    'commercial_registration_number' => ['required', 'string', 'max:255'],
                    'commercial_registration_image' => ['required', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
                    'national_address_image' => ['required', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
                    'tax_registration_number' => ['required', 'string', 'max:255'],
                ]);
                break;
        }

        // Ignore attributes when upload the App
        if (app()->environment('uploading')) {
            unset(
                $rules['personal_picture'],
                $rules['residence_name'],
                $rules['residence_number'],
                $rules['residence_image'],
                $rules['bank_name'],
                $rules['iban'],
                $rules['commercial_registration_number'],
                $rules['commercial_registration_image'],
                $rules['national_address_image'],
                $rules['tax_registration_number']
            );
        }

        $request->validate($rules);

        $user = new User();

        // Basic information
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = Hash::make($request->string('password'));
        $user->city_id = $request->city;
        $user->type = $request->account_type;

        // More information
        $user->entity_type = $request->entity_type;
        $user->residence_name = $request->residence_name;
        $user->residence_number = $request->residence_number;
        $user->bank_name = $request->bank_name;
        $user->iban = $request->iban;
        $user->commercial_registration_number = $request->commercial_registration_number;
        $user->tax_registration_number = $request->tax_registration_number;

        // Status
        $user->status = $request->account_type == User::USER_ACCOUNT_TYPE
            ? User::ACTIVE_STATUS
            : User::PENDING_STATUS;

        $user->save();

        if ($request->categories) $user->categories()->attach($request->categories);

        $singleImages = [
            'residence_image',
            'personal_picture',
            'commercial_registration_image',
            'national_address_image',
        ];

        // Ignore attributes when upload the App
        if (!app()->environment('uploading')) {
            foreach ($singleImages as $imageName) {
                if (isset($request[$imageName])) {
                    $user->addMediaFromRequest($imageName)
                        ->toMediaCollection($imageName == 'personal_picture' ? 'avatar' : $imageName);
                }
            }
        }

        NewUser::dispatch($user);

        $token = $user->createToken('api');

        return response([
            'token' => $token->plainTextToken,
            'user' => new UserResource($user)
        ]);
    }
}
