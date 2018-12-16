<?php

namespace App\Policies;

use App\User;
use App\Resource;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the resource.
     *
     * @param  \App\User  $user
     * @param  \App\Resource  $resource
     * @return mixed
     */
    public function view(User $user, Resource $resource)
    {
        return $resource->status != 0;
    }

    /**
     * Determine whether the user can create resources.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // TODO: add admin privileges
        return $user->id === $resource->id;
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @param  \App\User  $user
     * @param  \App\Resource  $resource
     * @return mixed
     */
    public function update(User $user, Resource $resource)
    {
        // TODO: add admin privileges
        return $user->id === $resource->id;
    }

    /**
     * Determine whether the user can delete the resource.
     *
     * @param  \App\User  $user
     * @param  \App\Resource  $resource
     * @return mixed
     */
    public function delete(User $user, Resource $resource)
    {
        // TODO: add admin privileges?
        return $user->id === $resource->id;
    }
}
