<?php

namespace App\Policies;

use App\Models\User;

class InvoicePolicy
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

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isServiceProvider($user);
    }
}
