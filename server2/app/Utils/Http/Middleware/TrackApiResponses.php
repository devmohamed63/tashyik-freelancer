<?php

namespace App\Utils\Http\Middleware;

use App\Utils\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class TrackApiResponses
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (app()->hasDebugModeEnabled() && !app()->environment('testing')) return $response;

        $status = $response->getStatusCode();

        switch ($status) {
            case 200:
            case 204:
                return $this->successResponse(json_decode($response->content(), true));

            case 201:
                return $this->createdResponse(json_decode($response->content(), true));

            case 400:
                return $this->badRequestResponse();

            case 401:
                return $this->unauthorizedResponse();

            case 403:
                return $this->forbiddenResponse(json_decode($response->content(), true));

            case 404:
                return $this->notFoundResponse();

            case 422:
                return $this->validationErrorResponse(json_decode($response->content(), true));

            case 500:
                return $this->serverErrorResponse();
        }

        return $response;
    }
}
