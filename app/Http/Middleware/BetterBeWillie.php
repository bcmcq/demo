<?php

namespace App\Http\Middleware;

use App\Models\User;

class BetterBeWillie extends Authenticate
{
    /** {@inheritdoc} */
    protected function authenticate($request, array $guards)
    {
        $user = User::find(1);

        $this->auth->guard()->setUser($user);
    }
}
