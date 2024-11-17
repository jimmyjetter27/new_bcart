<?php

namespace App\Jobs;

use App\Services\ImageHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeletePhotosFromCloudinary implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $photos;


    /**
     * Create a new job instance.
     */
    public function __construct($photos)
    {
        $this->photos = $photos;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imageHelper = app(ImageHelper::class);

        foreach ($this->photos as $photo) {
            if ($photo->isStoredInCloudinary()) {
                $imageHelper->deleteImage($photo->image_public_id);
            }
        }
    }
}
