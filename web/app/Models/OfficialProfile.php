<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OfficialProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class OfficialProfile extends Model
{
    /** @use HasFactory<OfficialProfileFactory> */
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'user_id',
        'service_number',
        'name',
        'rank',
        'rank_val',
        'designation',
        'branch',
        'status',
        'clearance_level',
        'specialization',
        'neo4j_synced_at',
    ];

    protected $casts = [
        'rank_val' => 'integer',
        'clearance_level' => 'integer',
        'neo4j_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
