<?php

namespace App\Policies;

use App\User;
use App\ResourceUse;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class ResourceUsePolicy
{
    use HandlesAuthorization;

    //if we want to implement admin system later, might be useful
    //give admins permission to do anything in the policy--check if admin before executing any other methods
    // public function before($user, $ability)
    // {
    //     if ($user->isSuperAdmin()) {
    //         return true;
    //     }
    // }

    /**
     * Determine whether the user can view the resourceUse.
     *
     * @param  \App\User  $user
     * @param  \App\ResourceUse  $resourceUse
     * @return mixed
     */
    public function view(User $user)
    {
        //anyone can view
        return true;
    }

    /**
     * Determine whether the user can store resourceUses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
        if (Auth::check()) {
            // The user is logged in...
            return true;
        }
    }

    /**
     * Determine whether the user can update the resourceUse.
     *
     * @param  \App\User  $user
     * @param  \App\ResourceUse  $resourceUse
     * @return mixed
     */
    // public function update(User $user, ResourceUse $resourceUse)
    // {
    //     //
    // }

    /**
     * Determine whether the user can delete the resourceUse.
     *
     * @param  \App\User  $user
     * @param  \App\ResourceUse  $resourceUse
     * @return mixed
     */
    public function delete(User $user, ResourceUse $resourceUse)
    {
        //TODO: implement admin here (or with the before function at the top)
        if (Auth::check()) {
            // The user is logged in...
            return false;
        }
    }
}
