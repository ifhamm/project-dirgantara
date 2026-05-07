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
        return in_array($user->role, ['admin', 'superadmin']);
    }

    public function delete(User $user, MwsPart $mwsPart): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }

    public function superadmin(User $user, MwsPart $mwsPart): bool
    {
        return $user->role === 'superadmin';
    }

    public function approvedStep(User $user, MwsPart $mwsPart): bool
    {
        return in_array($user->role, ['quality2', 'mechanic']);
    }

    public function approvedFinal(User $user, MwsPart $mwsPart): bool
    {
        return $user->role === 'quality1';
    }
}
