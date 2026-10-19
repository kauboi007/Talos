<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['service_id', 'dob', 'blood_group', 'street', 'city', 'state', 'pincode', 'rank'])]
class Soldier extends Model
{
    protected $keyType = 'string';

    public $incrementing = 'false';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
