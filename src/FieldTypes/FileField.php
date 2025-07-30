<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<FileField>
 */
class FileField extends FieldType
{
    protected string $type = 'file';

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'accept' => ['application/pdf'],
            'fileSize' => 1024,
            'is_required' => false,
            'caption' => '',
        ];
    }
}
