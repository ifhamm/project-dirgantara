<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MwsPart;

class MwsPartPolicy
{
    public function view(User $user, MwsPart $mwsPart): bool
    {
        return true;
    }

    public function update(User $user, MwsPart $mwsPart): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'quality1', 'quality2']);
    }

    public function delete(User $user, MwsPart $mwsPart): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }

    public function superadmin(User $user, MwsPart $mwsPart): bool
    {
        return $user->role === 'superadmin';
    }
}