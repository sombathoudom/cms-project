<?php

namespace App\Domains\Workflow\Models;

use App\Domains\Content\Models\Content;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishQueue extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'content_id',
        'scheduled_for',
        'status',
        'last_error',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
