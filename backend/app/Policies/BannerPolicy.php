<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;

class BannerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view banners');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Banner $banner): bool
    {
        return $user->can('view banners');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create banners');
    }

    /**
     * Determine whether the user can update any models.
     */
    public function updateAny(User $user): bool
    {
        return $user->can('update banners');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Banner $banner): bool
    {
        return $user->can('update banners');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Banner $banner): bool
    {
        return $user->can('delete banners');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete banners');
    }
}
