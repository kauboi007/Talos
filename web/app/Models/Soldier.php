<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['service_id', 'user_id', 'dob', 'bloodGroup', 'street', 'city', 'state', 'pincode', 'rank'])]
class Soldier extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
