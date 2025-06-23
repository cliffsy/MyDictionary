<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\TranslatableResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

// use PHPOpenSourceSaver\JWTAuth\JWTAuth;


class ValidToken
{
    use TranslatableResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * Check if user is authenticated
         */
        if (!auth()->check()) {
            return $this->translateFailedResponse(401, "Unauthenticated");
        }

        /**
         * Invalidate other user's token by checking
         * token's IAT against user's last login at
         */
        $token = null;
        $headers = $request->headers;
        if ($headers->has('Authorization')) {
            $token = explode(" ", $headers->get('Authorization'))[1];
        }
        $payload = JWTAuth::parseToken($token)->getPayload();
        $iat = $payload->get('iat');
        $user_id = $payload->get('sub');
        $user = User::find($user_id);
        if ($iat < $user->last_login_at) {
            return $this->translateFailedResponse(401, "Your account has been logged into another device");
        }

        return $next($request);
    }
}
