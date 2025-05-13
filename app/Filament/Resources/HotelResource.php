<?php

namespace App\Filament\Resources;

use App\Models\Hotel;
use App\Models\RoomType;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Қонақ үй және бөлмелер';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Қонақ үй бойынша ақпарат')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('address')->required()->maxLength(255),
                        Select::make('city_id')->label('Қала')->relationship('city','name')
                        ->searchable()
                        ->preload()
                        ->required(),
                        TextInput::make('country')->required()->maxLength(255),
                        Textarea::make('description')->required(),
                        TextInput::make('stars')->numeric()->minValue(1)->maxValue(5)->required(),
                        TextInput::make('price_per_night')->numeric()->minValue(0)->required(),
                        FileUpload::make('image')->image()->directory('hotels')->maxSize(2048),
                    ]),

                Section::make('Бөлме түрлері')
                    ->schema([
                        Repeater::make('roomTypes')
                            ->relationship()
                            ->minItems(1)
                            ->schema([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('price_per_night')->required()->numeric()->minValue(0),
                                TextInput::make('max_guests')->required()->numeric()->minValue(1),
                                TextInput::make('available_rooms')->required()->numeric()->minValue(0),
                                Textarea::make('description')->label('Сипаттамасы')->nullable(),
                                FileUpload::make('image')->label('Суреті')->image()->directory('room-types')->maxSize(2048),
                                Toggle::make('has_breakfast')->label('Таңғы ас'),
                                Toggle::make('has_wifi')->label('Wi-Fi'),
                                Toggle::make('has_tv')->label('Теледидар'),
                                Toggle::make('has_air_conditioning')->label('Кондиционер'),
                            ])
                            ->columns(2)
                            ->createItemButtonLabel('Бөлме түрін қосу'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('Қалалар')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country'),
                Tables\Columns\TextColumn::make('stars'),
                Tables\Columns\TextColumn::make('price_per_night')->money('KZT'),
                Tables\Columns\ImageColumn::make('image'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => HotelResource\Pages\ListHotels::route('/'),
            'create' => HotelResource\Pages\CreateHotel::route('/create'),
            'edit' => HotelResource\Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}
