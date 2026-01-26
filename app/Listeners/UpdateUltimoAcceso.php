<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class UpdateUltimoAcceso
{
    public function handle(Login $event): void
    {
        DB::table('users')
            ->where('id', $event->user->id)
            ->update(['ultimo_acceso' => now()]);
    }
}
