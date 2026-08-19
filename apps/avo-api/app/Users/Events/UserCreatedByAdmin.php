<?php

namespace App\Users\Events;

use App\Persistence\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Auth\Authenticatable;

class UserCreatedByAdmin
{
    use Dispatchable, SerializesModels;

    public $user;
    public $admin;

    public function __construct(User $user, Authenticatable $admin)
    {
        $this->user = $user;
        $this->admin = $admin;
    }
}
