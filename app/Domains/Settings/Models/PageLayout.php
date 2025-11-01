<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageLayout extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'view',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];
}
