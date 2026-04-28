<?php

namespace App\Models;

use App\Traits\SyncableWithDaftra;
use App\Utils\Services\Daftra;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, SyncableWithDaftra;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'view_token',
        'service_provider_id',
        'type',
        'action',
        'amount',
        'target_id',
        'event_uid',
        'daftra_id',
        'daftra_public_view_url',
        'daftra_payment_id',
        'daftra_invoice_pdf_sent_at',
        'recorded_in_daftra',
        'recorded_in_daftra_at',
        'recorded_in_daftra_by',
    ];

    /**
     * Available invoice types
     *
     * @var array
     */
    const AVAILABLE_TYPES = [
        self::COMPLETED_ORDER_TYPE,
        self::COMPLETED_ORDER_TAX_TYPE,
        self::ADDITIONAL_SERVICES_TYPE,
        self::ADDITIONAL_SERVICES_TAX_TYPE,
        self::RENEW_SUBSCRIPTION_TYPE,
        self::BANK_TRANSFER_TYPE,
        self::RENEW_SUBSCRIPTION_TAX_TYPE,
    ];

    /**
     * Available invoice actions
     *
     * @var array
     */
    const AVAILABLE_ACTIONS = [
        self::CREDIT_ACTION,
        self::DEBIT_ACTION,
    ];

    /**
     * Completed order type
     *
     * @var string
     */
    const COMPLETED_ORDER_TYPE = 'completed-order';

    /**
     * Completed order tax type
     *
     * @var string
     */
    const COMPLETED_ORDER_TAX_TYPE = 'completed-order-tax';

    /**
     * Additional services type
     *
     * @var string
     */
    const ADDITIONAL_SERVICES_TYPE = 'additional-services';

    /**
     * Additional services tax type
     *
     * @var string
     */
    const ADDITIONAL_SERVICES_TAX_TYPE = 'additional-services-tax';

    /**
     * Renew subscription type
     *
     * @var string
     */
    const RENEW_SUBSCRIPTION_TYPE = 'renew-subscription';

    /**
     * Renew subscription tax type
     *
     * @var string
     */
    const RENEW_SUBSCRIPTION_TAX_TYPE = 'renew-subscription-tax';

    /**
     * Bank transfer type
     *
     * @var string
     */
    const BANK_TRANSFER_TYPE = 'bank-transfer';

    /**
     * Credit action
     *
     * @var string
     */
    const CREDIT_ACTION = 'credit';

    /**
     * Debit action
     *
     * @var string
     */
    const DEBIT_ACTION = 'debit';

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            if ($invoice->view_token !== null && $invoice->view_token !== '') {
                return;
            }
            $invoice->view_token = self::generateUniqueViewToken();
        });
    }

    /**
     * Secret segment for short public invoice URLs (emails). Not guessable.
     */
    public static function generateUniqueViewToken(): string
    {
        for ($i = 0; $i < 30; $i++) {
            $token = Str::lower(Str::random(32));
            if (! self::query()->where('view_token', $token)->exists()) {
                return $token;
            }
        }

        throw new \RuntimeException('Unable to generate unique invoice view_token.');
    }

    /**
     * Get translated type.
     */
    protected function translatedType(): Attribute
    {
        $typeTranslations = [
            'ar' => [
                self::COMPLETED_ORDER_TYPE => 'طلب مكتمل',
                self::COMPLETED_ORDER_TAX_TYPE => 'رسوم طلب مكتمل',
                self::ADDITIONAL_SERVICES_TYPE => 'خدمات إضافية',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'رسوم خدمات إضافية',
                self::RENEW_SUBSCRIPTION_TYPE => 'تجديد الاشتراك',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'رسوم تجديد الاشتراك',
                self::BANK_TRANSFER_TYPE => 'تحويل بنكي',
            ],
            'en' => [
                self::COMPLETED_ORDER_TYPE => 'Completed Order',
                self::COMPLETED_ORDER_TAX_TYPE => 'Completed Order Tax',
                self::ADDITIONAL_SERVICES_TYPE => 'Additional Services',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'Additional Services Tax',
                self::RENEW_SUBSCRIPTION_TYPE => 'Renew subscription',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'Subscription renewal fee',
                self::BANK_TRANSFER_TYPE => 'Bank transfer',
            ],
            'hi' => [
                self::COMPLETED_ORDER_TYPE => 'पूर्ण आदेश',
                self::COMPLETED_ORDER_TAX_TYPE => 'पूर्ण आदेश कर',
                self::ADDITIONAL_SERVICES_TYPE => 'अतिरिक्त सेवाएँ',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'अतिरिक्त सेवाएँ कर',
                self::RENEW_SUBSCRIPTION_TYPE => 'सदस्यता नवीनीकृत हो गई',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'सदस्यता नवीनीकरण शुल्क',
                self::BANK_TRANSFER_TYPE => 'बैंक स्थानांतरण',
            ],
            'bn' => [
                self::COMPLETED_ORDER_TYPE => 'সম্পূর্ণ অর্ডার',
                self::COMPLETED_ORDER_TAX_TYPE => 'সম্পূর্ণ অর্ডার কর',
                self::ADDITIONAL_SERVICES_TYPE => 'অতিরিক্ত সেবা',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'অতিরিক্ত সেবা কর',
                self::RENEW_SUBSCRIPTION_TYPE => 'সাবস্ক্রিপশন নবায়ন হয়েছে',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'সাবস্ক্রিপশন নবায়ন ফি',
                self::BANK_TRANSFER_TYPE => 'ব্যাংক স্থানান্তর',
            ],
            'ur' => [
                self::COMPLETED_ORDER_TYPE => 'مکمل آرڈر',
                self::COMPLETED_ORDER_TAX_TYPE => 'مکمل آرڈر ٹیکس',
                self::ADDITIONAL_SERVICES_TYPE => 'اضافی سروسز',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'اضافی سروسز ٹیکس',
                self::RENEW_SUBSCRIPTION_TYPE => 'رکنیت تجدید ہوگئی ہے',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'رکنیت کی تجدید کی فیس',
                self::BANK_TRANSFER_TYPE => 'بینک ٹرانسفر',
            ],
            'tl' => [
                self::COMPLETED_ORDER_TYPE => 'Kumpletong Order',
                self::COMPLETED_ORDER_TAX_TYPE => 'Kumpletong Order Buwis',
                self::ADDITIONAL_SERVICES_TYPE => 'Karagdagang Serbisyo',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'Karagdagang Serbisyo Buwis',
                self::RENEW_SUBSCRIPTION_TYPE => 'Na-renew ang subscription',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'Bayad sa pag-renew ng subscription',
                self::BANK_TRANSFER_TYPE => 'Paglipat ng bangko',
            ],
            'id' => [
                self::COMPLETED_ORDER_TYPE => 'Pesanan Selesai',
                self::COMPLETED_ORDER_TAX_TYPE => 'Pajak Pesanan Selesai',
                self::ADDITIONAL_SERVICES_TYPE => 'Layanan Tambahan',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'Pajak Layanan Tambahan',
                self::RENEW_SUBSCRIPTION_TYPE => 'Langganan diperbarui',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'Biaya perpanjangan langganan',
                self::BANK_TRANSFER_TYPE => 'Transfer bank',
            ],
            'fr' => [
                self::COMPLETED_ORDER_TYPE => 'Commande Terminée',
                self::COMPLETED_ORDER_TAX_TYPE => 'Taxe Commande Terminée',
                self::ADDITIONAL_SERVICES_TYPE => 'Services Supplémentaires',
                self::ADDITIONAL_SERVICES_TAX_TYPE => 'Taxe Services Supplémentaires',
                self::RENEW_SUBSCRIPTION_TYPE => 'Abonnement renouvelé',
                self::RENEW_SUBSCRIPTION_TAX_TYPE => 'Frais de renouvellement d’abonnement',
                self::BANK_TRANSFER_TYPE => 'Virement bancaire',
            ],
        ];

        return Attribute::make(
            get: function () use ($typeTranslations) {
                $locale = app()->getLocale();

                return $typeTranslations[$locale][$this->type];
            },
        );
    }

    /**
     * Get the service provider.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Owner-console link in Daftra (staff). Requires company login — not for SP primary CTA; use {@see daftraRecipientWebUrl()}.
     */
    public function daftraInvoiceViewUrl(): ?string
    {
        if (! $this->daftra_id) {
            return null;
        }

        return app(Daftra::class)->ownerInvoiceViewUrl((int) $this->daftra_id);
    }

    /**
     * Daftra recipient-facing web URL for emails: canonical {@see Daftra::clientInvoiceViewUrl()} when synced;
     * otherwise optional URL from API stored in {@see $daftra_public_view_url} (rare).
     */
    public function daftraRecipientWebUrl(): ?string
    {
        if ($this->daftra_id) {
            return app(Daftra::class)->clientInvoiceViewUrl((int) $this->daftra_id);
        }

        $u = $this->daftra_public_view_url;

        return (is_string($u) && filter_var($u, FILTER_VALIDATE_URL)) ? $u : null;
    }

    /**
     * Primary link for invoice emails: Daftra client view when {@see $daftra_id} is set; optionally Tashyik public page
     * (see {@see config('services.tashyik.invoice_emails_include_local_public_link')}).
     */
    public function invoiceEmailPrimaryUrl(): ?string
    {
        $daftra = $this->daftraRecipientWebUrl();
        if ($daftra !== null) {
            return $daftra;
        }

        if ((bool) config('services.tashyik.invoice_emails_include_local_public_link', true)) {
            return $this->platformWebUrl();
        }

        return null;
    }

    /**
     * Persist {@see daftra_public_view_url} from Daftra GET invoice when still empty (API preview / PDF link — not used for email primary CTA).
     */
    public function fillDaftraPublicViewUrlFromApi(Daftra $daftra): bool
    {
        if ($this->daftra_public_view_url || ! $this->daftra_id) {
            return false;
        }

        $url = $daftra->fetchSalesInvoiceRecipientViewUrl((int) $this->daftra_id);
        if (! is_string($url) || $url === '') {
            return false;
        }

        $this->forceFill(['daftra_public_view_url' => $url])->saveQuietly();

        return true;
    }

    /**
     * URL to open this invoice in a normal browser (emails, Daftra notes).
     *
     * Default: short URL {@see route('public.invoices.token')} when {@see $view_token} is set;
     * otherwise a signed legacy URL. Override with TASHYIK_INVOICE_SHOW_URL for a custom host/path.
     *
     * @see config('services.tashyik.invoice_show_url')
     */
    public function platformWebUrl(): string
    {
        $template = config('services.tashyik.invoice_show_url');
        $id = (string) $this->id;

        if (is_string($template) && $template !== '') {
            return str_contains($template, '{id}')
                ? str_replace('{id}', $id, $template)
                : rtrim($template, '/').'/'.$id;
        }

        $token = $this->view_token;
        if (is_string($token) && $token !== '') {
            return URL::route('public.invoices.token', ['view_token' => $token], absolute: true);
        }

        $ttlDays = (int) config('services.tashyik.public_invoice_link_ttl_days', 1825);

        return URL::temporarySignedRoute(
            'public.invoices.show',
            now()->addDays(max(1, $ttlDays)),
            ['invoice' => $this->id],
            absolute: true
        );
    }
}
