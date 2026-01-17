<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;




class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

     public static function form(Schema $schema): Schema
     {
        //  return PostForm::configure($schema);
        return $schema
        ->components([
            \Filament\Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            \Filament\Forms\Components\Textarea::make('content')
            ->label('contenu')
            ->rows(6)
            ->columnSpanFull(),

            \Filament\Forms\Components\FileUpload::make('file_path')
            ->label('Fichier (PDF/ Image)')
            ->disk('private')
            ->directory('posts')
            ->acceptedFileTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
            ])
            ->maxSize(5120)
            ->visibility('private'),

            \Filament\Forms\Components\Hidden::make('user_id')
                // ->default(fn ()=>Filament::auth()->id()),
                ->default(fn () => \Filament\Facades\Filament::auth()->id()),
            ]);
    }

   

    public static function table(Table $table): Table
    {
        // return PostsTable::configure($table);
        return $table 
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('title')
            ->searchable()
            ->sortable(),

            \Filament\Tables\Columns\BadgeColumn::make('status')
              ->label('Statut')
    ->formatStateUsing(fn (string $state) => match ($state) {
        \App\Models\Post::STATUS_DRAFT => 'Brouillon',
        \App\Models\Post::STATUS_PENDING => 'En attente',
        \App\Models\Post::STATUS_APPROVED => 'Approuvée',
        \App\Models\Post::STATUS_REJECTED => 'Rejetée',
        default => $state,
    })
                 ->colors([
    'secondary' => Post::STATUS_DRAFT,
    'warning'   => Post::STATUS_PENDING,
    'success'   => Post::STATUS_APPROVED,
    'danger'    => Post::STATUS_REJECTED,
]),
               


                    \Filament\Tables\Columns\TextColumn::make('user.name')
                        ->label('auteur')
                        ->sortable(),

                    \Filament\Tables\Columns\TextColumn::make('created_at')
                        ->label('Créé le')
                        ->dateTime()
                        ->sortable(),


            

            ]);

            
                    // ->actions([
                    //     // \Filament\Tables\Actions\EditAction::make(),
                    //     EditAction::make(),

                    // ]);
            // ->actions([ 
            //     // Editer : uniquement brouillon ou rejeté
            //     Action::make('edit')
            //         ->label('Modifier')
            //         ->icon('heroicon-o-pencil')
            //         ->url(fn ($record) => static::getUrl('edit',['record' => $record]))
            //         ->visible(fn ($record) =>
            //             Filament::auth()->user()->hasRole('redacteur')
            //             && in_array($record->status, ['Brouillion','Rejetée'])
            // ),
            // //Soumettre a la validation
            // Action::make('submit')
            // ->label('Soumettre')
            // ->icon('heroicon-o-paper-airplane')
            // ->color('warning')
            // ->RequiresCofirmation()
            // ->visible(fn ($record)=>
            //     Filament::auth()->user()->hasRole('redacteur')
            //     && $record->status ==='Brouillion'
            //     )
            // ->action(function ($record){
            //     $record ->update([
            //         'status'=>'En attente',
            //     ]);
            // }),

            // //Approuver
            // Action::make('approve')
            // ->label('Approuver')
            // ->icon('heroicon-o-check-circle')
            // ->color('success')
            //  ->RequiresCofirmation()
            // ->visible(fn ($record)=>
            //     Filament::auth()->user()->hasRole('validateur')
            //     && $record->status ==='En attente'
            //     )
            // ->action(function ($record){
            //     $record->update([
            //         'status'=>'approuvée',
            //         'validated_by'=>Filament::auth()->id(),
            //         'validate_at'=>now(),
            //     ]);
            // }),


            // //Rejeter
            // Action::make('reject')
            // ->label('Rejeter')
            // ->icon('heroicon-o-x-circle')
            // ->color('danger')
            //  ->RequiresCofirmation()
            // ->visible(fn ($record)=>
            //     Filament::auth()->user()->hasRole('validateur')
            //     && $record->status ==='En attente'
            //     )
            // ->action(function ($record){
            //     $record->update([
            //         'status'=>'rejecter',
            //         'validated_by'=>Filament::auth()->id(),
            //         'validate_at'=>now(),
            //     ]);
            // }),






           // ]);
                    

    }

    

    // public static function canViewAny(): bool
    // {
    //     return auth()->user()?->hasAnyRole([
    //         'super-admin',
    //         'redacteur',
    //         'validateur',
    //     ]);
    // }

//     public static function canViewAny(): bool
// {
//     // return Filament::auth()
//     //     ->user()
//     //     ?->hasAnyRole([
//     //         'super-admin',
//     //         'redacteur',
//     //         'validateur',
//     //     ]) ?? false;
// }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
