<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();
        
        if ($user && $user->isAdmin()) {
            return redirect()->intended('/admin');
        }
        
        return redirect()->intended('/vendor');
    }
}

