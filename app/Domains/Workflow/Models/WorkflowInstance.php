<?php

namespace App\Domains\Workflow\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'workflow_id',
        'workflowable_type',
        'workflowable_id',
        'current_step_code',
        'status',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }
}
