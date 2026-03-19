<?php

namespace App\Services\Field;

use App\Enums\AttributeType;
use App\Enums\DimensionUnit;
use App\Enums\WeightUnit;

class FieldTypeRegistry
{
    public function getMetadata(AttributeType $type): array
    {
        return match ($type) {
            AttributeType::Text, AttributeType::Number, AttributeType::Boolean => [
                'input' => ['fields' => ['value']],
                'validation' => [],
            ],
            AttributeType::Color => [
                'input' => ['fields' => ['value']],
                'validation' => ['lookups' => 'colors'],
            ],
            AttributeType::Dimensions => [
                'input' => ['unit' => DimensionUnit::Centimeter, 'fields' => ['height', 'width', 'depth']],
                'validation' => [
                    'min' => ['default' => 0, 'required' => true, 'type' => 'number'],
                    'max' => ['type' => 'number'],
                ],
            ],
            AttributeType::Weight => [
                'input' => ['unit' => WeightUnit::Kilogram, 'fields' => ['value']],
                'validation' => [
                    'min' => ['default' => 0, 'required' => true, 'type' => 'number'],
                    'max' => ['type' => 'number'],
                ],
            ],
        };
    }

    public function getValidationRules(AttributeType $type, array $config = []): array
    {
        // Here you return the actual Laravel validation rules based on the type
        return match ($type) {
            AttributeType::Number => ['numeric'],
            AttributeType::Boolean => ['boolean'],
            AttributeType::Dimensions => ['array', 'size:3'],
            default => ['string'],
        };
    }
}
