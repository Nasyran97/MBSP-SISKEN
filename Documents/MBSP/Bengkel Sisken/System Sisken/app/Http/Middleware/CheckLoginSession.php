<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('user_id')) {
            return redirect()->route('signin', [
                'redirectUrl' => $request->getRequestUri(),
            ])->with('error', 'Sila masukkan No Staf dan kata laluan untuk log masuk semula.');
        }

        return $next($request);
    }
}