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
        'name',
        'email',
        'password',
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

    public $keyType = 'varchar';

    public $incrementing = false;


    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function setNameAttribute($value)
    {
        return $this->attributes['name'] = ucwords($value);
    }
}
