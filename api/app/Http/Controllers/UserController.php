<?php

namespace App\Http\Controllers;

use App\Constant\Status;
use App\Events\UpdateAccessRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $this->checkUserPermission('users');
        return $this->fetch(User::class, $request);
    }

    public function getPermissions()
    {
        try {
            if (Auth::user()->status != Status::USER_ACTIVE) {
                return $this->translateFailedResponse(401, "Invalid");
            }
            $permissions = Auth::user()->getAllPermissions();
            return $this->translateSuccessResponse(null, $permissions);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function getRoles()
    {
        try {
            if (Auth::user()->status != Status::USER_ACTIVE) {
                return $this->translateFailedResponse(401, "Invalid");
            }
            $permissions = Auth::user()->getRoleNames();
            return $this->translateSuccessResponse(null, $permissions);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function create()
    {
        try {
            $this->checkUserPermission('users-create');

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
            $user->status = Status::USER_EMAIL_VERIFICATION;

            $this->syncAccessRoles($user, request());

            $user->save();

            event(new Registered($user));

            return $this->translateSuccessResponse("Registered successfully!", $user);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        try {
            $this->checkUserPermission('users');
            $user = User::find($id)->load('roles');
            return $this->translateSuccessResponse(null, $user);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        try {
            $this->checkUserPermission('users-id-update');

            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'roles' => 'required',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($id)->where('is_deleted', 0)
                ],
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(400, $validator->messages()->first());
            }


            $user = User::find($id);
            $user->fill($request->all());

            /**
             * Validate password
             */
            if (isset($request->password) || isset($request->password_confirmation)) {
                $validator = Validator::make(request()->all(), [
                    'password' => 'required|confirmed|min:8',
                ]);

                if ($validator->fails()) {
                    return $this->translateFailedResponse(400, $validator->messages()->first());
                }

                $user->password = bcrypt($request->password);
            }


            $this->syncAccessRoles($user, $request);

            $user->save();


            event(new UpdateAccessRole($user, $user->getAllPermissions()));

            return $this->translateSuccessResponse("User successfully updated", $user);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function syncAccessRoles($user, $request)
    {
        $role_ids = array_map(function ($role) {
            return isset($role['id']) ? $role['id'] : $role;
        }, $request->roles);
        $user->syncRoles($role_ids);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $this->checkUserPermission('users-delete');
            $user = User::find($id)->delete();
            DB::commit();
            return $this->translateSuccessResponse("User successfully deleted", $user);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->translateExceptionResponse($e);
        }
    }
}
