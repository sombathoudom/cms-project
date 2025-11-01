<?php

namespace App\Domains\Workflow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'workflow_instance_id',
        'from_step',
        'to_step',
        'action',
        'actor_id',
        'note',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
