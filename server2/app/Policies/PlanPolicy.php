<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    /**
     * Determine whether the service provider can subscribe to the plan.
     */
    public function subscribe(User $user, Plan $plan): bool
    {
        return $user->type == $plan->target_group;
    }
}
