<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentStatus extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'code',
        'label',
    ];
}
