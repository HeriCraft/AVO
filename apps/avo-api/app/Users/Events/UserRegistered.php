<?php

namespace App\Users\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Persistence\Models\User;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public $user;
    public $ipAddress;

    public function __construct(User $user, $ipAddress)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
    }
}
