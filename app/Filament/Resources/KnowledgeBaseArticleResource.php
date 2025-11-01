<?php

namespace App\Filament\Resources;

use App\Domains\Content\Models\KnowledgeBaseArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KnowledgeBaseArticleResource extends Resource
{
    protected static ?string $model = KnowledgeBaseArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?string $operation): bool => $operation === 'edit'),
                Forms\Components\Textarea::make('excerpt')
                    ->columnSpanFull()
                    ->rows(3)
                    ->live(onBlur: true),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                        'h2',
                        'h3',
                        'link',
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->live(debounce: '1000ms'),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('publish_at')
                    ->native(false)
                    ->seconds(false)
                    ->live(onBlur: true),
                Forms\Components\View::make('filament.components.content-editor-tools')
                    ->columnSpanFull()
                    ->viewData(['interval' => 60000]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('publish_at')->dateTime(),
                Tables\Columns\TextColumn::make('author.name')->label('Author'),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => KnowledgeBaseArticle::query()
                        ->whereNotNull('tenant_id')
                        ->distinct()
                        ->pluck('tenant_id', 'tenant_id')
                        ->toArray()),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->action(function (KnowledgeBaseArticle $record, Action $action): void {
                        $user = auth()->user();

                        if (! $user) {
                            abort(401);
                        }

                        $link = app(\App\Domains\Content\Services\ContentEditorService::class)->createPreviewLink($record, $user);

                        $action->dispatchBrowserEvent('content-preview.open', [
                            'url' => $link['url'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => KnowledgeBaseArticleResource\Pages\ListKnowledgeBaseArticles::route('/'),
            'create' => KnowledgeBaseArticleResource\Pages\CreateKnowledgeBaseArticle::route('/create'),
            'edit' => KnowledgeBaseArticleResource\Pages\EditKnowledgeBaseArticle::route('/{record}/edit'),
        ];
    }
}
