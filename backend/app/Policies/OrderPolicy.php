<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Check if user account type is service provider
     *
     * @param User $user
     * @return bool
     */
    private function isServiceProvider($user): bool
    {
        return $user->type == User::SERVICE_PROVIDER_ACCOUNT_TYPE;
    }

    private function ensureSubscriptionIsActive($user): ?string
    {
        // Ignore the proccess when uploading the app
        if (app()->environment('uploading')) return null;

        // Check if service provider belongs to institution
        if ($user->institution_id) {
            $subscription = $user->institution->subscription;
        } else {
            $subscription = $user->subscription;
        }

        // Check if subscription expired
        if ($subscription?->ends_at <= now()) {
            $message = [
                'ar' => 'يجب تجديد الاشتراك للمتابعة',
                'en' => 'Subscription renewal required to continue',
                'hi' => 'जारी रखने के लिए सदस्यता नवीनीकरण आवश्यक है',
                'bn' => 'চালিয়ে যেতে সাবস্ক্রিপশন নবায়ন প্রয়োজন',
                'ur' => 'جاری رکھنے کے لیے رکنیت کی تجدید ضروری ہے',
                'tl' => 'Kinakailangan ang pag-renew ng subscription upang magpatuloy',
                'id' => 'Perpanjangan langganan diperlukan untuk melanjutkan',
                'fr' => 'Renouvellement de l’abonnement requis pour continuer',
            ];

            return $message[app()->getLocale()];
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders') || $this->isServiceProvider($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Check if user is manager
        if ($user->can('manage orders')) return true;

        // Check if the order is new
        if ($order->status == Order::NEW_STATUS) {
            return $this->isServiceProvider($user) || $order->customer_id == $user->id;
        }

        // Allow the order's owner and the assigned service provider to view the order
        return $order->service_provider_id == $user->id || $order->customer_id == $user->id;
    }

    /**
     * Determine whether the service provider can update order status.
     */
    public function updateStatus(User $user, Order $order, string $status)
    {
        $updateableStatusTypes = [
            Order::NEW_STATUS,
            Order::SERVICE_PROVIDER_ON_THE_WAY,
            Order::SERVICE_PROVIDER_ARRIVED,
            Order::STARTED_STATUS,
            Order::COMPLETED_STATUS,
        ];

        // Get current status index
        $currentIndex = array_search($order->status, $updateableStatusTypes);

        // Get new status index
        $newIndex = array_search($status, $updateableStatusTypes);

        $statusAccepted = $newIndex > $currentIndex;

        // Ignore provider id check for unassigned orders
        if ($status == Order::SERVICE_PROVIDER_ON_THE_WAY) {
            $error = $this->ensureSubscriptionIsActive($user);

            // Check if subscription expired before accept order
            abort_if((bool) $error, 402, $error);

            return $statusAccepted;
        }

        return $order->service_provider_id == $user->id && $statusAccepted;
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $order->customer_id == $user->id
            && $order->status == Order::NEW_STATUS
            && $order->created_at <= now()->subMinutes(Order::CANCEL_WAITING_TIME);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('manage orders');
    }
}
