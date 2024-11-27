<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasUuids,SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $guarded = [];
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

    public function getUser()
    {
        return $this->hasMany(User::class, 'company_id','id')->whereNull('deleted_at');
    }

    public static function setData($request)
    {
        $data = [
            'name'     => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
        ];

        return $data;
    }
}
