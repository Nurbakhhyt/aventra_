<?php

namespace App\Filament\Resources;

use App\Models\Location;
use App\Models\City;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin'; // Камера вместо фотографии
    protected static ?string $navigationGroup = 'Администрирование';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Название локации')
                    ->required()
                    ->maxLength(255),

                Select::make('city_id')
                    ->label('Город')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->nullable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Локация')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('Город')
                    ->searchable()
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
            'index' => LocationResource\Pages\ListLocations::route('/'),
            'create' => LocationResource\Pages\CreateLocation::route('/create'),
            'edit' => LocationResource\Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
