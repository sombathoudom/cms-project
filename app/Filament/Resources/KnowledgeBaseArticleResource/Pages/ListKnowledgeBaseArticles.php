<?php

namespace App\Filament\Resources\KnowledgeBaseArticleResource\Pages;

use App\Filament\Resources\KnowledgeBaseArticleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\CreateAction;

class ListKnowledgeBaseArticles extends ListRecords
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
