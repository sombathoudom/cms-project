<?php

namespace App\Filament\Resources\Concerns;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Services\ContentEditorService;
use Illuminate\Support\Arr;

trait HandlesContentAutoSave
{
    protected string $autoSaveBaselineHash = '';

    protected int $autoSaveIntervalSeconds = 60;

    protected function initializeAutoSaveBaseline(): void
    {
        $this->autoSaveBaselineHash = $this->hashState($this->form->getState());
    }

    public function autoSaveDraft(): array
    {
        $user = auth()->user();

        if (! $user) {
            abort(401, 'Authentication required');
        }

        /** @var Content $content */
        $content = $this->getRecord();

        if ($user->cannot('update', $content)) {
            abort(403, 'Insufficient permissions to auto-save this content.');
        }

        $state = $this->form->getState();
        $hash = $this->hashState($state);

        if ($hash === $this->autoSaveBaselineHash) {
            return ['skipped' => true];
        }

        $service = app(ContentEditorService::class);
        $service->autoSave($content, $state, $user);

        $this->autoSaveBaselineHash = $hash;

        return [
            'skipped' => false,
            'saved_at' => now()->toIso8601String(),
        ];
    }

    protected function hashState(array $state): string
    {
        $normalized = Arr::sortRecursive($state);

        return hash('sha256', json_encode($normalized));
    }
}
