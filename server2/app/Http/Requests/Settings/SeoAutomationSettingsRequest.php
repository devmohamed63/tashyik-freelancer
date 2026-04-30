<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class SeoAutomationSettingsRequest extends FormRequest
{
    //comment
    protected $redirect = '';

    public function __construct()
    {
        $this->redirect = \Illuminate\Support\Facades\Route::has('dashboard.articles.index')
            ? route('dashboard.articles.index', ['tab' => 'seo-automation'])
            : route('dashboard.settings.index', ['tab' => 'seo-automation']);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ai_blog_automation_enabled' => ['nullable', 'boolean'],
            'ai_blog_daily_limit' => ['required', 'integer', 'min:1', 'max:100'],
            'ai_blog_monthly_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'ai_blog_prompt' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
