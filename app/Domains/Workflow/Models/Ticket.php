<?php

namespace App\Domains\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Ticket extends Model
{
    use HasFactory;
    use HasUuids;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'reference',
        'subject',
        'description',
        'status',
        'priority',
        'requester_email',
        'assigned_to',
        'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function toSearchableArray(): array
    {
        return [
            'subject' => $this->subject,
            'description' => $this->description,
            'reference' => $this->reference,
            'requester_email' => $this->requester_email,
        ];
    }
}
