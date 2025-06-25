<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Traits\TranslatableResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
                'name' => [
                    'required',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('is_deleted', 0);
                    })
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('is_deleted', 0);
                    })
                ],
                'password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(400, $validator->messages()->first());
            }

            $user = new User();
            $user->name = request()->name;
            $user->email = request()->email;

            if (isset(request()->password) && request()->password) {
                $user->password = bcrypt(request()->password);
            }

            $user->save();

            return $this->translateSuccessResponse("You're all set. Click the Sign In button below and start exploring words your way!", $user);
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
            $data = request()->user();
            return $this->translateSuccessResponse(null, $data);
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
            Auth::user()->tokens()->delete();
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
