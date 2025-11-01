<?php

namespace App\Filament\Resources\KnowledgeBaseArticleResource\Pages;

use App\Filament\Resources\Concerns\HandlesContentAutoSave;
use App\Filament\Resources\KnowledgeBaseArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeBaseArticle extends EditRecord
{
    use HandlesContentAutoSave;

    protected static string $resource = KnowledgeBaseArticleResource::class;

    protected ?string $previewUrl = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->initializeAutoSaveBaseline();
    }

    protected function afterSave(): void
    {
        $this->initializeAutoSaveBaseline();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview Draft')
                ->color('gray')
                ->action('openPreview')
                ->icon('heroicon-o-eye'),
        ];
    }

    public function openPreview(): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        $link = app(\App\Domains\Content\Services\ContentEditorService::class)->createPreviewLink($this->getRecord(), $user);
        $this->previewUrl = $link['url'];

        $this->dispatchBrowserEvent('content-preview.open', [
            'url' => $this->previewUrl,
        ]);
    }
}
