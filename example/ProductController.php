<?php

namespace App\Http\Controllers;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\InfoLists\InfoListBuilder;
use AntiCmsBuilder\InfoLists\Section;
use AntiCmsBuilder\InfoLists\Entries\TextEntry;
use AntiCmsBuilder\InfoLists\Entries\BooleanEntry;
use AntiCmsBuilder\InfoLists\Entries\ImageEntry;
use AntiCmsBuilder\InfoLists\Entries\RelationshipEntry;
use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Traits\UseCrudController;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\FieldTypes\SelectField;
use AntiCmsBuilder\Tables\Columns\TextColumn;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    use UseCrudController;

    protected string $model = Product::class;

    public function forms(FormBuilder $builder): FormBuilder
    {
        return $builder->forms([
            InputField::make()
                ->name('name')
                ->label('Product Name')
                ->placeholder('Enter product name')
                ->required()
                ->multilanguage()
                ->toArray(),

            SelectField::make()
                ->name('category_id')
                ->label('Category')
                ->placeholder('Select category')
                ->loadOptionFromRelation('category', 'name')
                ->toArray(),

            InputField::make()
                ->name('price')
                ->label('Price')
                ->type('number')
                ->step('0.01')
                ->min(0)
                ->required()
                ->toArray(),

            BooleanEntry::make()
                ->name('is_active')
                ->label('Active Status')
                ->toArray(),
        ]);
    }

    public function tables(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->query(fn (Builder $query) => $query->with('category'))
            ->columns([
                TextColumn::make()
                    ->name('name')
                    ->searchable()
                    ->sortable()
                    ->toArray(),

                TextColumn::make()
                    ->name('category.name')
                    ->label('Category')
                    ->toArray(),

                TextColumn::make()
                    ->name('price')
                    ->label('Price')
                    ->format(fn ($value) => '$' . number_format($value, 2))
                    ->toArray(),
            ]);
    }

    public function infoList(InfoListBuilder $builder): InfoListBuilder
    {
        return $builder
            ->record($builder->record)
            ->sections([
                Section::make('Product Information')
                    ->entries([
                        TextEntry::make()
                            ->name('name')
                            ->label('Product Name')
                            ->toArray(),

                        TextEntry::make()
                            ->name('price')
                            ->label('Price')
                            ->format(fn ($value) => $value ? '$' . number_format($value, 2) : '—')
                            ->toArray(),

                        BooleanEntry::make()
                            ->name('is_active')
                            ->label('Active Status')
                            ->toArray(),
                    ])
                    ->toArray(),

                Section::make('Relationships')
                    ->entries([
                        RelationshipEntry::make()
                            ->name('category')
                            ->label('Category')
                            ->displayUsing('name')
                            ->toArray(),
                    ])
                    ->toArray(),

                Section::make('Media')
                    ->entries([
                        ImageEntry::make()
                            ->name('featured_image')
                            ->label('Featured Image')
                            ->toArray(),
                    ])
                    ->toArray(),
            ])
            ->entries([
                TextEntry::make()
                    ->name('created_at')
                    ->label('Created Date')
                    ->format(fn ($value) => $value ? $value->format('M d, Y H:i') : '—')
                    ->toArray(),

                TextEntry::make()
                    ->name('updated_at')
                    ->label('Last Updated')
                    ->format(fn ($value) => $value ? $value->format('M d, Y H:i') : '—')
                    ->toArray(),
            ]);
    }
}