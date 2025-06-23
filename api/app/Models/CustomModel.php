<?php

namespace App\Models;

use App\Events\ModelNewData;
use App\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CustomModel extends BaseModel
{

    use SoftDeletes, HasUuids;

    protected $casts = [
        'id' => 'string'
    ];

    protected $hidden = [
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    public $keyType = 'varchar';
    public $incrementing = false;

    // public function __construct()
    // {
    //     parent::__construct();
    // }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->from_mobile) {
                unset($model->from_mobile);
            } else {
                $column_existing = Schema::hasColumn($model::getTableName(), 'created_by');
                if ($column_existing && Auth::guard('api')->check()) {
                    $model->created_by = Auth::guard('api')->user()->id;
                } else if ($column_existing) {
                    $model->created_by = Auth::id();
                }
            }
        });

        static::created(function ($model) {
            /**
             * Load all model's relationships
             */
            $relationships = $model->relationships;
            if ($relationships) {
                $model->load($relationships);
            }
            event(new ModelNewData(
                class_basename($model::class),
                $model
            ));
        });

        static::updating(function ($model) {
            if ($model->from_mobile) {
                unset($model->from_mobile);
            } else {
                $column_existing = Schema::hasColumn($model::getTableName(), 'updated_by');
                if ($column_existing && Auth::guard('api')->check()) {
                    $model->updated_by = Auth::guard('api')->user()->id;
                } else if ($column_existing) {
                    $model->updated_by = Auth::id();
                }

                $model->updated_at = date('Y-m-d H:i:s');
            }
        });

        static::deleting(function ($model) {
            if ($model->from_mobile) {
                unset($model->from_mobile);
            } else {
                $column_existing = Schema::hasColumn($model::getTableName(), 'deleted_by');
                if ($column_existing && Auth::guard('api')->check()) {
                    $model->deleted_by = Auth::guard('api')->user()->id;
                } else if ($column_existing) {
                    $model->deleted_by = Auth::id();
                }
                $column_existing_at = Schema::hasColumn($model::getTableName(), 'deleted_at');
                if ($column_existing_at) {
                    $model->deleted_at = date('Y-m-d H:i:s');
                }
            }
        });
    }
}
