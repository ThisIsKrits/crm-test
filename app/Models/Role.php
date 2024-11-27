<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $guarded = [];
}
