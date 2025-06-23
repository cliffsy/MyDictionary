<?php

namespace App\Models;

use App\Events\ModelNewData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_login_at',
        'created_at',
        'created_by',
        'deleted_at',
        'deleted_by',
        "is_deleted",

        'updated_at',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        "full_name"
    ];

    public $keyType = 'varchar';

    public $incrementing = false;


    public static function boot()
    {
        parent::boot();
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
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function setFirstNameAttribute($value)
    {
        return $this->attributes['first_name'] = ucwords($value);
    }

    public function setLastNameAttribute($value)
    {
        return $this->attributes['last_name'] = ucwords($value);
    }
}
