<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable([
    'name',
    'troop_name',
    'created_by',
    'approved_by',
    'approved_at',
    'status',
    'feasible',
    'planning_result',
])]
class Mission extends Model
{
    protected function casts(): array
    {
        return [
            'feasible' => 'boolean',
            'planning_result' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
