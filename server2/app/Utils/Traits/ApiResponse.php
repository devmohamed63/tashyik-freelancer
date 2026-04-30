<?php

namespace App\Utils\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function apiResponse(
        $data = null,
        array $message = [
            'ar' => '', # Arabic
            'en' => '', # English
            'hi' => '', # Hindi
            'bn' => '', # Bengali
            'ur' => '', # Pakistan - Urdu
            'tl' => '', # Filipino - Tagalog
            'id' => '', # Indonesian
            'fr' => '', # French
        ],
        bool $status = true,
        int $code = 200
    ): JsonResponse {
        $data = is_array($data) ? $data : ['payload' => $data];

        $locale = app()->getLocale();

        $supportedLocales = config('app.available_locales');

        // Validate locale
        if (!in_array($locale, $supportedLocales)) {
            return response()->json([
                'status'  => false,
                'code'  => 422,
                'message' => 'Locale (' . $locale . ') must be one of the following: [' . implode(', ', $supportedLocales) . ']'
            ], 422);
        }

        return response()->json([
            'status'  => $status,
            'code'  => $code,
            'message' => $message[$locale],
            ...$data,
        ], $code);
    }

    protected function successResponse($data = null, array $message = [
        'ar' => 'تم بنجاح',
        'en' => 'Success',
        'hi' => 'सफलता',
        'bn' => 'সাফল্য',
        'ur' => 'کامیابی',
        'tl' => 'Tagumpay',
        'id' => 'Sukses',
        'fr' => 'Succès',
    ]): JsonResponse
    {
        return $this->apiResponse($data, $message, true, 200);
    }

    protected function createdResponse($data = null, array $message = [
        'ar' => 'تم الإنشاء بنجاح',
        'en' => 'Created successfully',
        'hi' => 'सफलतापूर्वक बनाया गया',
        'bn' => 'সফলভাবে তৈরি হয়েছে',
        'ur' => 'کامیابی کے ساتھ بنایا گیا',
        'tl' => 'Matagumpay na nalikha',
        'id' => 'Berhasil dibuat',
        'fr' => 'Créé avec succès',
    ]): JsonResponse
    {
        return $this->apiResponse($data, $message, true, 201);
    }

    protected function badRequestResponse(array $message = [
        'ar' => 'طلب غير صالح',
        'en' => 'Bad request',
        'hi' => 'गलत अनुरोध',
        'bn' => 'খারাপ অনুরোধ',
        'ur' => 'غلط درخواست',
        'tl' => 'Masamang kahilingan',
        'id' => 'Permintaan buruk',
        'fr' => 'Mauvaise requête',
    ]): JsonResponse
    {
        return $this->apiResponse(null, $message, false, 400);
    }

    protected function unauthorizedResponse(array $message = [
        'ar' => 'غير مصرح به',
        'en' => 'Unauthorized',
        'hi' => 'अनधिकृत',
        'bn' => 'অননুমোদিত',
        'ur' => 'غیر مجاز',
        'tl' => 'Walang awtorisasyon',
        'id' => 'Tidak sah',
        'fr' => 'Non autorisé',
    ]): JsonResponse
    {
        return $this->apiResponse(null, $message, false, 401);
    }

    protected function forbiddenResponse($data = null, array $message = [
        'ar' => 'ممنوع',
        'en' => 'Forbidden',
        'hi' => 'निषिद्ध',
        'bn' => 'নিষিদ্ধ',
        'ur' => 'ممنوع',
        'tl' => 'Ipinagbabawal',
        'id' => 'Dilarang',
        'fr' => 'Interdit',
    ]): JsonResponse
    {
        return $this->apiResponse(['payload' => $data], $message, false, 403);
    }

    protected function notFoundResponse(array $message = [
        'ar' => 'غير موجود',
        'en' => 'Not found',
        'hi' => 'नहीं मिला',
        'bn' => 'পাওয়া যায়নি',
        'ur' => 'نہیں ملا',
        'tl' => 'Hindi natagpuan',
        'id' => 'Tidak ditemukan',
        'fr' => 'Non trouvé',
    ]): JsonResponse
    {
        return $this->apiResponse(null, $message, false, 404);
    }

    protected function validationErrorResponse(array $errors, array $message = [
        'ar' => 'فشل التحقق من صحة البيانات',
        'en' => 'Validation failed',
        'hi' => 'मान्यकरण विफल',
        'bn' => 'যাচাই ব্যর্থ হয়েছে',
        'ur' => 'تصدیق ناکام ہوگئی',
        'tl' => 'Nabigo ang pagpapatunay',
        'id' => 'Validasi gagal',
        'fr' => 'Échec de la validation',
    ]): JsonResponse
    {
        return $this->apiResponse(['errors' => $errors], $message, false, 422);
    }

    protected function serverErrorResponse(array $message = [
        'ar' => 'خطأ في الخادم',
        'en' => 'Server error',
        'hi' => 'सर्वर त्रुटि',
        'bn' => 'সার্ভার ত্রুটি',
        'ur' => 'سرور کی خرابی',
        'tl' => 'Error sa server',
        'id' => 'Kesalahan server',
        'fr' => 'Erreur du serveur',
    ]): JsonResponse
    {
        return $this->apiResponse(null, $message, false, 500);
    }
}
