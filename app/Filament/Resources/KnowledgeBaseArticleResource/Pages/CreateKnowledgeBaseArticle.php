<?php

namespace App\Filament\Resources\KnowledgeBaseArticleResource\Pages;

use App\Filament\Resources\KnowledgeBaseArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;

class CreateKnowledgeBaseArticle extends CreateRecord
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    #[On('content-editor-insert-media')]
    public function appendMedia(array $payload): void
    {
        $markup = trim((string) ($payload['markup'] ?? ''));

        if ($markup === '') {
            return;
        }

        $state = $this->form->getState();
        $existingBody = trim((string) Arr::get($state, 'body', ''));
        $updatedBody = $existingBody !== '' ? $existingBody."\n\n".$markup : $markup;

        $this->form->fill(array_merge($state, ['body' => $updatedBody]));
    }
}
