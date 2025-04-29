<?php

namespace App\Filament\Resources;

use App\Models\Tour;
use App\Models\Location;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase'; // Иконка "чемодан"
    protected static ?string $navigationGroup = 'Администрирование';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Название тура')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Описание тура')
                    ->required()
                    ->maxLength(65535),

                Select::make('user_id')
                    ->label('Организатор')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable()
                    ->preload(),

                Select::make('location_id')
                    ->label('Локация')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->nullable()
                    ->preload(),

                TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->required(),

                TextInput::make('volume')
                    ->label('Количество мест')
                    ->numeric()
                    ->required(),

                TextInput::make('date')
                    ->label('Дата проведения')
                    ->required(),

                FileUpload::make('image')
                    ->label('Изображение тура')
                    ->image()
                    ->directory('tours') // Куда будут загружаться файлы
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Организатор')
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Локация')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена')
                    ->money('kzt') // Или выберите свою валюту

                    ->sortable(),

                TextColumn::make('volume')
                    ->label('Мест'),

                TextColumn::make('date')
                    ->label('Дата тура')
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('Фото')
                    ->circular(), // Красивая круглая форма картинки
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
            'index' => TourResource\Pages\ListTours::route('/'),
            'create' => TourResource\Pages\CreateTour::route('/create'),
            'edit' => TourResource\Pages\EditTour::route('/{record}/edit'),
        ];
    }
}
