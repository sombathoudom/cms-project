<?php

namespace App\Domains\Content\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ContentPreviewLink extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'content_id',
        'token',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasExpired(): bool
    {
        /** @var Carbon|null $expiresAt */
        $expiresAt = $this->expires_at;

        return $expiresAt !== null && $expiresAt->isPast();
    }
}
