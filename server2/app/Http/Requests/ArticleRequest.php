<?php

namespace App\Http\Requests;

use App\Models\Article;
use App\Models\Service;
use App\Utils\HtmlToPlainText;
use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status
                ? Article::ACTIVE_STATUS
                : Article::INACTIVE_STATUS,
            'is_featured' => $this->boolean('is_featured'),
        ]);

        if ($this->filled('service_id')) {
            $service = Service::query()->find((int) $this->input('service_id'));
            if ($service) {
                $this->merge(['category_id' => $service->category_id]);
            }
        } elseif ($this->has('service_id')) {
            $this->merge(['service_id' => null, 'category_id' => null]);
        }

        if ($this->has('body') && is_array($this->input('body'))) {
            $this->merge([
                'meta_description' => [
                    'ar' => HtmlToPlainText::convertString((string) ($this->input('body.ar') ?? '')),
                    'en' => HtmlToPlainText::convertString((string) ($this->input('body.en') ?? '')),
                ],
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title.*' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:articles,slug,' . $this->route('article')?->id],
            'excerpt.*' => ['required', 'string', 'max:500000'],
            'body.*' => ['required', 'string', 'max:5000000'],
            'meta_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:5000000'],
            'status' => ['nullable'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'featured_image' => ['image', 'mimes:' . config('app.allowed_image_mimes', 'webp,png,jpg,jpeg'), 'max:' . config('app.upload_max_size', 5120)],
        ];

        if ($this->isMethod('PUT')) {
            array_push($rules['featured_image'], 'nullable');
        } else {
            array_push($rules['featured_image'], 'required');
        }

        return $rules;
    }
}
