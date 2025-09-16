<?php

use AntiCmsBuilder\InfoLists\InfoListBuilder;
use AntiCmsBuilder\InfoLists\Entries\FileEntry;
use AntiCmsBuilder\InfoLists\Entries\ImageEntry;
use AntiCmsBuilder\InfoLists\Entries\TextEntry;

/**
 * Example showing how to use the enhanced file auto-generation in InfoLists
 */
class FileInfoListExample
{
    /**
     * Example with automatic file detection (no manual configuration needed)
     */
    public function autoDetectionExample($record)
    {
        return InfoListBuilder::make(User::class)
            ->record($record)
            // No entries defined - will auto-generate based on record data
            // Files will be automatically detected and rendered appropriately:
            // - Images: thumbnail preview with fallback download link
            // - Videos: video player with fallback download link  
            // - Audio: audio player with file name and size
            // - Documents: download link with file icon and size
            // - Archives: download link with archive icon and size
            // - Generic files: download link with generic file icon
            ->build();
    }

    /**
     * Example with explicit file entries for more control
     */
    public function explicitFileEntriesExample($record)
    {
        return InfoListBuilder::make(User::class)
            ->record($record)
            ->entries([
                TextEntry::make('name')->label('Name'),
                
                // Auto-detecting file entry - will determine type and render appropriately
                FileEntry::make('profile_image')
                    ->label('Profile Image')
                    ->height(150)
                    ->width(150)
                    ->circular(),
                
                // Force specific file type
                FileEntry::make('resume')
                    ->label('Resume')
                    ->fileType('document')
                    ->downloadable()
                    ->showSize(),
                
                // Media field that auto-detects type
                FileEntry::make('portfolio_video')
                    ->label('Portfolio Video')
                    ->height(200),
                
                // Traditional image entry
                ImageEntry::make('avatar')
                    ->label('Avatar')
                    ->height(100)
                    ->width(100)
                    ->square(),
            ])
            ->build();
    }

    /**
     * Example showing different file types that are auto-detected
     */
    public function fileTypesExample()
    {
        // Sample data structure that would be auto-detected:
        
        $sampleRecord = [
            // Will be detected as 'image' and show thumbnail
            'profile_photo' => [
                'url' => 'https://example.com/photos/user.jpg',
                'name' => 'user.jpg',
                'size' => 245760,
                'mime_type' => 'image/jpeg'
            ],
            
            // Will be detected as 'video' and show video player
            'intro_video' => [
                'url' => 'https://example.com/videos/intro.mp4',
                'name' => 'intro.mp4',
                'size' => 15728640,
                'mime_type' => 'video/mp4'
            ],
            
            // Will be detected as 'audio' and show audio player
            'voice_note' => [
                'url' => 'https://example.com/audio/note.mp3',
                'name' => 'voice_note.mp3',
                'size' => 2097152,
                'mime_type' => 'audio/mpeg'
            ],
            
            // Will be detected as 'document' and show download link
            'cv_file' => [
                'url' => 'https://example.com/docs/cv.pdf',
                'name' => 'John_Doe_CV.pdf',
                'size' => 524288,
                'mime_type' => 'application/pdf'
            ],
            
            // Will be detected as 'archive' and show download link
            'project_files' => [
                'url' => 'https://example.com/files/project.zip',
                'name' => 'project_files.zip', 
                'size' => 10485760,
                'mime_type' => 'application/zip'
            ],
            
            // Simple URL - will detect by extension
            'portfolio_image' => 'https://example.com/images/portfolio.png',
            
            // Spatie Media Library object - will be handled automatically
            'gallery_photo' => $mediaLibraryObject, // Media model instance
        ];
        
        return $sampleRecord;
    }
}