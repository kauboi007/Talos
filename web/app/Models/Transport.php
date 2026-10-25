<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable([
    'vehicle_no',
    'name',
    'vehicle_type',
    'kind',
    'manufacturer',
    'model',
    'manufacture_year',
    'speed_kmh',
    'fuel_type',
    'crew_capacity',
    'passenger_capacity',
    'cargo_capacity_kg',
    'range_km',
    'status',
])]
class Transport extends Model
{
    protected $primaryKey = 'vehicle_no';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'manufacture_year' => 'integer',
            'speed_kmh' => 'decimal:2',
            'crew_capacity' => 'integer',
            'passenger_capacity' => 'integer',
            'cargo_capacity_kg' => 'integer',
            'range_km' => 'decimal:2',
        ];
    }
}
