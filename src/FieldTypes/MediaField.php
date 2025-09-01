<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<MediaField>
 */
class MediaField extends FieldType
{
    protected string $type = 'media';

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'accept' => [
                'image/png', 
                'image/jpeg', 
                'image/jpg', 
                'image/gif', 
                'image/svg+xml', 
                'image/webp',
                'video/mp4',
                'video/avi',
                'video/mov',
                'audio/mp3',
                'audio/wav',
                'audio/ogg'
            ],
            'fileSize' => 5120, // 5MB default
            'is_required' => false,
            'caption' => '',
            'multiple' => false,
        ];
    }

    /**
     * @return MediaField
     */
    public function multiple(bool $multiple = true): static
    {
        $this->attributes['multiple'] = $multiple;

        return $this;
    }

    /**
     * @return MediaField
     */
    public function maxFileSize(int $sizeInKb): static
    {
        $this->attributes['fileSize'] = $sizeInKb;

        return $this;
    }

    /**
     * @return MediaField
     */
    public function acceptedTypes(array $types): static
    {
        $this->attributes['accept'] = $types;

        return $this;
    }
}