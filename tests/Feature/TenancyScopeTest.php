<?php

use App\Domains\Content\Models\Content;
use App\Domains\Workflow\Models\Ticket;
use Illuminate\Support\Str;

it('scopes data by tenant id', function () {
    $tenantA = (string) Str::uuid();
    $tenantB = (string) Str::uuid();

    Content::factory()->count(2)->create(['tenant_id' => $tenantA]);
    Content::factory()->count(1)->create(['tenant_id' => $tenantB]);

    expect(Content::query()->where('tenant_id', $tenantA)->count())->toBe(2);
    expect(Content::query()->where('tenant_id', $tenantB)->count())->toBe(1);

    Ticket::factory()->count(3)->create(['tenant_id' => $tenantA]);
    expect(Ticket::query()->where('tenant_id', $tenantA)->count())->toBe(3);
});
