<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoritesController extends Controller
{
    public function list()
    {
        $model = Favorite::where('user_id', Auth::user()->id);
        return $this->fetch(Favorite::class, request(), $model);
    }

    public function show($word)
    {
        try {
            $data = Favorite::where('word', $word)->where('user_id', Auth::user()->id)->first();
            return $this->fetchOne($data, request());
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function create()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'word' => [
                    'required',
                    Rule::unique('favorites')->where(function ($query) {
                        return $query->where('user_id', Auth::id())->where('is_deleted', 0);
                    })
                ],
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(400, $validator->messages()->first());
            }

            $data = Favorite::create([
                ...request()->all(),
                "user_id" => Auth::user()->id
            ]);

            return $this->translateSuccessResponse("Word definition saved to your favorites", $data);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function update($id)
    {
        try {
            $data = Favorite::find($id)->update(request()->all());
            return $this->translateSuccessResponse("Word definition saved to your favorites", $data);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $user = Favorite::find($id)->delete();
            DB::commit();
            return $this->translateSuccessResponse("Word definition successfully removed from you favorites", $user);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->translateExceptionResponse($e);
        }
    }
}
