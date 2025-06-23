<?php

namespace App\Http\Controllers;

use App\Traits\TranslatableResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use TranslatableResponse;
    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::user();
                return $this->translateSuccessResponse("Welcome, $user->name", $user);
            } else {
                throw new Exception("Invalid credentials");
            }
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }

    }
}
