<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class AdminNotifier
{
    /**
     * @return Collection<int, User>
     */
    public function recipients(): Collection
    {
        return User::admins()->get();
    }

    public function notify(Notification $notification): void
    {
        foreach ($this->recipients() as $admin) {
            $admin->notify($notification);
        }
    }
}
