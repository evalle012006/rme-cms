<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Step 2 must not copy a photo PHP never wrote to disk. An oversized upload
 * used to open the working directory and crash with errno 21.
 */
class ReviewPhotoUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public_form_media');
    }

    public function testRejectedPhotoReturnsASizeErrorInsteadOfCrashing()
    {
        $file = new UploadedFile(
            '',
            'Screenshot 2026-09-24 at 3.33.37 PM.png',
            'image/png',
            UPLOAD_ERR_INI_SIZE,
            true
        );

        $response = $this->from('/review-step-2')->post(route('review-save-two'), [
            'review_image' => $file,
        ]);

        $response->assertRedirect('/review-step-2');
        $response->assertSessionHasErrors('review_image');
        $this->assertStringContainsString(
            (string) ini_get('upload_max_filesize'),
            session('errors')->first('review_image')
        );
        $response->assertSessionMissing('review-image');
        $this->assertSame([], Storage::disk('public_form_media')->allFiles());
    }

    public function testValidPhotoIsStoredUnderAHashedName()
    {
        $file = UploadedFile::fake()->image('Screenshot 2026-09-24 at 3.33.37 PM.png');
        $name = $file->hashName();

        $response = $this->from('/review-step-2')->post(route('review-save-two'), [
            'review_image' => $file,
        ]);

        $response->assertRedirect('/review-step-2');
        $response->assertSessionDoesntHaveErrors('review_image');
        $response->assertSessionHas('review-image', url('/') . '/review-form-media/' . $name);
        $response->assertSessionHas('review-image-system', public_path('review-form-media/' . $name));
        Storage::disk('public_form_media')->assertExists($name);
        $this->assertNotSame('Screenshot 2026-09-24 at 3.33.37 PM.png', $name);
    }
}
