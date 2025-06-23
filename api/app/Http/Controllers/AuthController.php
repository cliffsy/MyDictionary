<?php

namespace App\Http\Controllers;

use App\Models\AccessRole;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Constant\Status;
use App\Events\AuthEvent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Traits\TranslatableResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use TranslatableResponse;


    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->where('is_deleted', 0)
                ],
                'password' => !auth()->check() ? 'required|min:8' : '',
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(400, $validator->messages()->first());
            }

            $user = new User;
            $user->first_name = request()->first_name;
            $user->last_name = request()->last_name;
            $user->middle_name = request()->middle_name;
            $user->email = request()->email;

            if (isset(request()->password) && request()->password) {
                $user->password = bcrypt(request()->password);
            }

            return $this->translateSuccessResponse("Registered successfully!", $user);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {

            /**
             * Validate inputs
             */
            $validator = Validator::make(request()->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(401, $validator->messages()->first());
            }

            /**
             * Login attempt
             */
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return $this->translateFailedResponse(401, "Wrong email or password");
            }

            $user = User::find(Auth::user()->id);
            $user->last_login_at = time();
            $user->save();

            $response = $this->respondWithToken($user);
            return $this->translateSuccessResponse("Login successfully!", $response);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            return $this->translateSuccessResponse(null, auth()->user());
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();
            return $this->translateSuccessResponse("Successfully logged out");
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($user)
    {
        return [
            'access_token' => $user->createToken('access_token')->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24 * 7,
            'user' => Auth::user()
        ];
    }
}
