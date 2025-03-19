<?php

namespace App\Listeners;

use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RevokeOldLoginTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $user = User::find($event->userId);
        $user->tokens()->limit(PHP_INT_MAX)->offset(1)->get()->map(function ($token) {
            $token->revoke();
            $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        });
    }
}
