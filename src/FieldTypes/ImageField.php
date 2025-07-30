<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<ImageField>
 */
class ImageField extends FieldType
{
    protected string $type = 'image';

    // public static function make(): self
    // {
    //     return new self('', '');
    // }

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'accept' => ['image/png', 'image/jpeg'],
            'fileSize' => 1024,
            'resolution' => [
                'minWidth' => 100,
                'maxWidth' => 1000,
                'minHeight' => 100,
                'maxHeight' => 1000,
            ],
            'is_required' => false,
            'caption' => '',
        ];
    }

    /**
     * @return ImageField
     */
    public function height(int $min = 1, int $max = 100): static
    {
        $this->attributes['resolution']['minHeight'] = $min;
        $this->attributes['resolution']['maxHeight'] = $max;

        return $this;
    }

    /**
     * @return ImageField
     */
    public function width(int $min = 1, int $max = 100): static
    {
        $this->attributes['resolution']['minWidth'] = $min;
        $this->attributes['resolution']['maxWidth'] = $max;

        return $this;
    }
}
