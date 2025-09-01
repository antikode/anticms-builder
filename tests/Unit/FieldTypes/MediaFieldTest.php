<?php

namespace AntiCmsBuilder\Tests\Unit\FieldTypes;

use AntiCmsBuilder\FieldTypes\MediaField;
use AntiCmsBuilder\Tests\TestCase;

class MediaFieldTest extends TestCase
{
    public function testCanCreateMediaFieldInstance()
    {
        $field = MediaField::make();
        
        $this->assertInstanceOf(MediaField::class, $field);
    }

    public function testMediaFieldHasCorrectDefaultType()
    {
        $field = MediaField::make()->name('media')->label('Media');
        $array = $field->toArray();
        
        $this->assertEquals('media', $array['field']);
    }

    public function testCanSetMultiple()
    {
        $field = MediaField::make()->name('media')->multiple();
        $array = $field->toArray();
        
        $this->assertTrue($array['attribute']['multiple']);
    }

    public function testCanSetMaxFileSize()
    {
        $field = MediaField::make()->name('media')->maxFileSize(10240);
        $array = $field->toArray();
        
        $this->assertEquals(10240, $array['attribute']['fileSize']);
    }

    public function testCanSetAcceptedTypes()
    {
        $types = ['image/png', 'video/mp4'];
        $field = MediaField::make()->name('media')->acceptedTypes($types);
        $array = $field->toArray();
        
        $this->assertEquals($types, $array['attribute']['accept']);
    }

    public function testHasDefaultAttributes()
    {
        $field = MediaField::make()->name('media');
        $array = $field->toArray();
        
        $this->assertIsArray($array['attribute']['accept']);
        $this->assertContains('image/png', $array['attribute']['accept']);
        $this->assertContains('video/mp4', $array['attribute']['accept']);
        $this->assertContains('audio/mp3', $array['attribute']['accept']);
        $this->assertEquals(5120, $array['attribute']['fileSize']);
        $this->assertFalse($array['attribute']['is_required']);
        $this->assertFalse($array['attribute']['multiple']);
    }

    public function testCanChainMethods()
    {
        $field = MediaField::make()
            ->name('media')
            ->label('Media Files')
            ->multiple()
            ->maxFileSize(8192)
            ->required();
            
        $array = $field->toArray();
        
        $this->assertEquals('media', $array['name']);
        $this->assertEquals('Media Files', $array['label']);
        $this->assertTrue($array['attribute']['multiple']);
        $this->assertEquals(8192, $array['attribute']['fileSize']);
        $this->assertTrue($array['attribute']['is_required']);
    }
}