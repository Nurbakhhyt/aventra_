<?php

namespace App\Filament\Resources;

use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office'; // Иконка города
    protected static ?string $navigationGroup = 'Администрирование';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Название города')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('image')
                    ->label('Изображение города')
                    ->directory('cities') // Папка хранения файлов
                    ->image()
                    ->nullable(),

                Textarea::make('description')
                    ->label('Описание города')
                    ->nullable(),

                TextInput::make('city_code')
                    ->label('Код города')
                    ->required()
                    ->maxLength(3)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Изображение')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city_code')
                    ->label('Код')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => CityResource\Pages\ListCities::route('/'),
            'create' => CityResource\Pages\CreateCity::route('/create'),
            'edit' => CityResource\Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
