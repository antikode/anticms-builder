<?php

namespace App\Http\Controllers;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Traits\UseCrudController;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\FieldTypes\SelectField;
use AntiCmsBuilder\FieldTypes\TextareaField;
use AntiCmsBuilder\FieldTypes\ToggleField;

/**
 * Example controller demonstrating the usage of visibleWhen and hideWhen methods
 * for conditional field visibility in forms.
 * 
 * Note: Replace Controller and Product::class with your actual base controller and model
 */
class ConditionalFieldsExample
{
    use UseCrudController;

    // Replace with your actual model class
    // protected string $model = Product::class;

    public function forms(FormBuilder $builder): FormBuilder
    {
        return $builder->forms([
            // Example 1: Show/hide based on select field value
            SelectField::make()
                ->name('product_type')
                ->label('Product Type')
                ->placeholder('Select product type')
                ->options([
                    ['label' => 'Physical', 'value' => 'physical'],
                    ['label' => 'Digital', 'value' => 'digital'],
                    ['label' => 'Service', 'value' => 'service'],
                ])
                ->required()
                ->toArray(),

            // This field is only visible when product_type is 'physical'
            InputField::make()
                ->name('weight')
                ->label('Weight (kg)')
                ->type('number')
                ->placeholder('Enter product weight')
                ->visibleWhen('product_type', 'physical')
                ->toArray(),

            // This field is only visible when product_type is 'digital'
            InputField::make()
                ->name('download_link')
                ->label('Download Link')
                ->type('url')
                ->placeholder('Enter download URL')
                ->visibleWhen('product_type', 'digital')
                ->toArray(),

            // Example 2: Show field based on multiple values using 'in' operator
            InputField::make()
                ->name('shipping_cost')
                ->label('Shipping Cost')
                ->type('number')
                ->placeholder('Enter shipping cost')
                ->visibleWhen('product_type', ['physical', 'service'], 'in')
                ->toArray(),

            // Example 3: Toggle-based conditional visibility
            ToggleField::make()
                ->name('has_discount')
                ->label('Has Discount?')
                ->toArray(),

            // Show discount fields only when has_discount is true
            InputField::make()
                ->name('discount_percentage')
                ->label('Discount Percentage')
                ->type('number')
                ->placeholder('Enter discount percentage')
                ->min(0)
                ->max(100)
                ->visibleWhen('has_discount', true)
                ->toArray(),

            InputField::make()
                ->name('discount_start_date')
                ->label('Discount Start Date')
                ->type('date')
                ->visibleWhen('has_discount', true)
                ->toArray(),

            InputField::make()
                ->name('discount_end_date')
                ->label('Discount End Date')
                ->type('date')
                ->visibleWhen('has_discount', true)
                ->toArray(),

            // Example 4: Using hideWhen instead of visibleWhen
            SelectField::make()
                ->name('stock_status')
                ->label('Stock Status')
                ->options([
                    ['label' => 'In Stock', 'value' => 'in_stock'],
                    ['label' => 'Out of Stock', 'value' => 'out_stock'],
                    ['label' => 'Pre-order', 'value' => 'pre_order'],
                ])
                ->required()
                ->toArray(),

            // Hide this field when stock_status is 'out_stock'
            InputField::make()
                ->name('stock_quantity')
                ->label('Stock Quantity')
                ->type('number')
                ->placeholder('Enter stock quantity')
                ->hideWhen('stock_status', 'out_stock')
                ->toArray(),

            // Example 5: Using comparison operators
            InputField::make()
                ->name('price')
                ->label('Price')
                ->type('number')
                ->min(0)
                ->required()
                ->toArray(),

            // Show wholesale pricing only for products over $100
            InputField::make()
                ->name('wholesale_price')
                ->label('Wholesale Price')
                ->type('number')
                ->placeholder('Enter wholesale price')
                ->visibleWhen('price', 100, '>')
                ->toArray(),

            // Example 6: Multiple conditions can be chained
            ToggleField::make()
                ->name('is_featured')
                ->label('Featured Product?')
                ->toArray(),

            TextareaField::make()
                ->name('featured_description')
                ->label('Featured Description')
                ->placeholder('Enter featured product description')
                ->rows(4)
                ->visibleWhen('is_featured', true)
                ->visibleWhen('product_type', 'out_stock', '!=')
                ->toArray(),

            // Example 7: Using not_in operator
            InputField::make()
                ->name('special_handling_note')
                ->label('Special Handling Note')
                ->placeholder('Enter special handling instructions')
                ->visibleWhen('product_type', ['digital'], 'not_in')
                ->toArray(),
        ]);
    }
}
