<?php

namespace App\Services\Gemini;

use App\Events\NewUser;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ServiceProviderChatGuestRegistrar
{
    /**
     * @return array{ok: true, user: User}|array{ok: false, message: string, failed_field: ?string}
     */
    public function register(string $name, string $email, string $password, string $phoneForDb, int $cityId): array
    {
        if (! City::query()->whereKey($cityId)->exists()) {
            return [
                'ok' => false,
                'message' => 'المدينة المختارة غير صالحة. اختر رقم مدينة من القائمة.',
                'failed_field' => 'city',
            ];
        }

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phoneForDb,
                'city' => $cityId,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', Password::defaults()],
                'phone' => ['required', 'digits:10', 'unique:users,phone'],
                'city' => ['required', 'integer', 'exists:cities,id'],
            ],
            [],
            [
                'name' => 'الاسم',
                'email' => 'البريد',
                'password' => 'كلمة المرور',
                'phone' => 'رقم الجوال',
                'city' => 'المدينة',
            ]
        );

        if ($validator->fails()) {
            return $this->validationFailureToArabic(new ValidationException($validator));
        }

        $validated = $validator->validated();

        $user = new User;
        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->city_id = (int) $validated['city'];
        $user->type = User::USER_ACCOUNT_TYPE;
        $user->status = User::ACTIVE_STATUS;
        $user->save();

        NewUser::dispatch($user);

        return ['ok' => true, 'user' => $user];
    }

    /**
     * @return array{ok: false, message: string, failed_field: ?string}
     */
    private function validationFailureToArabic(ValidationException $e): array
    {
        $errors = $e->errors();
        $firstKey = array_key_first($errors);
        $firstMsg = $firstKey !== null ? (string) ($errors[$firstKey][0] ?? '') : 'تعذر إتمام التسجيل.';

        $failedField = $firstKey;
        if ($firstKey === 'email') {
            $firstMsg = 'هذا البريد الإلكتروني مسجّل عندنا مسبقًا. ارسل بريدًا آخر صالحًا.';
        } elseif ($firstKey === 'phone') {
            $firstMsg = 'رقم الجوال إما غير صحيح أو مسجّل مسبقًا. تأكد من ١٠ أرقام بصيغة سعودية (مثل 05xxxxxxxx) أو جرّب رقمًا آخر.';
        } elseif ($firstKey === 'password') {
            $failedField = 'password';
            $firstMsg = 'كلمة المرور لا تطابق شروط الأمان في التطبيق. قوّيها (أحرف وأرقام وطول كافٍ) وحاول مرة ثانية.';
        } elseif ($firstKey === 'name') {
            $firstMsg = 'الاسم غير صالح. أرسل اسمك الكامل بشكل أوضح.';
        } elseif ($firstKey === 'city') {
            $firstMsg = 'المدينة غير صالحة. اختر رقم id من القائمة أو اسم مدينة واضح.';
        }

        return [
            'ok' => false,
            'message' => $firstMsg,
            'failed_field' => is_string($failedField) ? $failedField : null,
        ];
    }
}
