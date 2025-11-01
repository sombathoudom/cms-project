<?php

namespace App\Http\Resources;

use App\Domains\Security\Models\AuditLog;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuditLog */
class AuditLogResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'action' => $this->action,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'actor' => $this->whenLoaded('actor', function () {
                return [
                    'id' => $this->actor?->id,
                    'name' => $this->actor?->name,
                    'email' => $this->actor?->email,
                ];
            }),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'payload' => $this->payload,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
