<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginWithTokenResponse implements BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Generate Sanctum token for the authenticated user
        $user = auth()->user();
        
        if ($user) {
            // Create a new token for the user
            $token = $user->createToken('filament-admin-token');
            
            // Store token in session for later use
            session(['sanctum_token' => $token->plainTextToken]);
            
            // Optional: Store token in user's profile or log it
            logger()->info('User logged in with token', [
                'user_id' => $user->id,
                'token_name' => 'filament-admin-token',
                'token_id' => $token->accessToken->id
            ]);
        }
        
        // Redirect to the intended page or dashboard
        return redirect()->intended(filament()->getUrl());
    }
}