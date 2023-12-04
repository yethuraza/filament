<?php

namespace App\Models;

use Filament\Forms;
use App\Enums\Region;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conference extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'start_data' => 'datetime',
        'end_data' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            Section::make('Conference Details')
                ->description('Provide some basic information about the conference')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    MarkdownEditor::make('description')
                        ->required()
                        ->columnSpanFull(),
                    DatePicker::make('start_data')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->required(),
                    DateTimePicker::make('end_data')
                        ->native(false)
                        ->displayFormat('Y-m-d D h:m:s a')
                        ->required(),

                    Fieldset::make()
                        ->schema([
                            Select::make('status')
                                ->columnSpanFull()
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Published',
                                    'archived' => 'Archived'
                                ])
                                ->required(),

                            CheckboxList::make('speakers')
                                ->columnSpanFull()
                                ->columns(3)
                                ->searchable()
                                ->relationship('speakers', 'name')
                                ->options(
                                    Speaker::all()->pluck('name', 'id')
                                )
                                ->required(),

                            Toggle::make('is_published')
                                ->default(true),
                        ])
                        ->columns(2),
                ]),

            Section::make('Locations')
                ->columns(2)
                ->schema([
                    Select::make('region')
                        ->live()
                        ->enum(Region::class)
                        ->options(Region::class),

                    Select::make('venue_id')
                        ->searchable()
                        ->preload()
                        ->createOptionForm(Venue::getForm())
                        ->editOptionForm(Venue::getForm())
                        ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                            return $query->where('region', $get('region'));
                        })
                        ->required(),
                ]),

            Actions::make([
                Action::make('star')
                    ->label('Fill form with factory data')
                    ->icon('heroicon-m-star')
                    ->visible(function (string $operation) {
                        if ($operation !== 'create')
                            return false;

                        if (!app()->environment('local')) {
                            return false;
                        }
                        return true;
                    })
                    ->action(function ($livewire) {
                        $data = Conference::factory()->make()->toArray();
                        $livewire->form->fill($data);
                    }),

            ]),
        ];
    }
}
