<?php

namespace Database\Factories\Domains\Content\Models;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Models\ContentPreviewLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentPreviewLink>
 */
class ContentPreviewLinkFactory extends Factory
{
    protected $model = ContentPreviewLink::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'created_by' => User::factory(),
        ];
    }
}
