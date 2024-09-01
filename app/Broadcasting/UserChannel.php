<?php

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, $userId): array|bool
    {
        return (int) $user->id === (int) $userId;
    }
}
