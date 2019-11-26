<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckDocumentNotEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \App\Document $document */
        $document = $request->route('document');

        if (!$document) {
            return $next($request);
        }

        if (!$document->payload) {
            throw new BadRequestHttpException('You cannot publish an empty document');
        }

        return $next($request);
    }
}
