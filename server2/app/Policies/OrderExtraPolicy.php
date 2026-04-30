<?php

namespace App\Policies;

use App\Models\User;

class OrderExtraPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->type == User::SERVICE_PROVIDER_ACCOUNT_TYPE;
    }
}
