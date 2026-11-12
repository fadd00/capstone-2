<?php

namespace App\Http\Middleware;

use App\Models\StatusForm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFormStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $statusForm = StatusForm::first();

        if ($statusForm && ! $statusForm->isOpen()) {
            return redirect()->route('home')->with('error', 'Pendaftaran PPDB saat ini sedang ditutup.');
        }

        return $next($request);
    }
}
