<?php

use App\Models\User;

function saludo(): string
{
    $hour = intval(now()->format("H"));
    if ($hour < 12) {
        return 'Buenos días,';
    } elseif ($hour <= 18 && $hour >= 12) {
        return 'Buenas tardes,';
    } else {
        return 'Buenas noches,';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?User
    {
        return auth()->user();
    }
}


function udd(...$args)
{
    $user = auth_user();
    if ($user->debug) {
        return dd($args);
    }
}
