<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'uuid';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'company_id',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getCompany()
    {
        return $this->belongsTo(Company::class, 'company_id','id');
    }

    public function getEmployee()
    {
        return $this->hasOne(Employee::class, 'user_id','id');
    }

    public function getRole()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    protected $searchField = ['name', 'email'];

    public function scopeBySearch($query, $value)
    {
        if ($value) {
            $query->where(function ($q) use ($value) {
                foreach ($this->searchField as $field) {
                    if (strpos($field, '.') !== false) {
                        list($relation, $relatedField) = explode('.', $field);
                        $q->orWhereHas($relation, function ($query) use ($relatedField, $value) {
                            $query->where($relatedField, 'like', '%' . $value . '%');
                        });
                    } else {
                        $q->orWhere($field, 'like', '%' . $value . '%');
                    }
                }
            });
        }
    }

    public static function setData($request)
    {
        $data = [
            'email'         => $request->email,
            'role_id'       => $request->role_id,
            'company_id'    => $request->company_id,
        ];

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        return $data;
    }
}
