<?php

namespace App\Jobs;

use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $design;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = 'public';
        $filename = $this->design->image;
        $original_file = storage_path() . '/designs/original/' . $filename;
        //dd($original_file);
        try {
            //create the large image and save it in the disk
            $img = Image::make($original_file);
            $img->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($large = storage_path('designs/large/') . $filename);
            $img->resize(250, 200, function ($constraint) {
                $constraint->aspectRatio();
            })->save($thumbnail = storage_path('designs/thumbnail/') . $filename);
            // store image to prentment disk
            // original image
            if (Storage::disk($disk)
                ->put('designs/original/' . $filename, fopen($original_file, 'r+'))
            ) {
                File::delete($original_file);
            }
            // large image
            if (Storage::disk($disk)
                ->put('designs/large/' . $filename, fopen($large, 'r+'))
            ) {
                File::delete($large);
            }
            // thumbnail image
            if (Storage::disk($disk)
                ->put('designs/thumbnail/' . $filename, fopen($thumbnail, 'r+'))
            ) {
                File::delete($thumbnail);
            }
            // update the database for upload_successful
            $this->design->update([
                'upload_successful' => true,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
