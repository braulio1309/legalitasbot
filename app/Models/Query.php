<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Query extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question',
        'response',
        'model_used',
        'token_count',
        'cost',
        'response_time',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost' => 'decimal:6'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}