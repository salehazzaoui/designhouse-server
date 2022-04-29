<?php

namespace App\Http\Controllers\Design;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Jobs\UploadImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,bmp', 'max:2048']
        ]);
        $image = $request->file('image');
        $filename = time() . '_' . preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));
        $image->storeAs('designs/original', $filename, 'public');
        $userId = Auth::user()->id;
        $user = User::find($userId);
        $design = $user->designs()->create([
            'image' => $filename
        ]);
        $directory_large = storage_path('app/public/designs/large');
        $directory_thumbnail = storage_path('app/public/designs/thumbnail');
        if (!File::exists($directory_large)) {
            Storage::makeDirectory('public/designs/large');
        }
        if (!File::exists($directory_thumbnail)) {
            Storage::makeDirectory('public/designs/thumbnail');
        }

        $original_file = storage_path() . '/app/public/designs/original/' . $filename;
        //dd($original_file);
        $img = Image::make($original_file);
        $img->resize(800, 600, function ($constraint) {
            $constraint->aspectRatio();
        })->save($large = storage_path('app/public/designs/large/') . $filename);
        $img->resize(250, 200, function ($constraint) {
            $constraint->aspectRatio();
        })->save($thumbnail = storage_path('app/public/designs/thumbnail/') . $filename);
        //UploadImage::dispatch($design);
        $design->update([
            'upload_successful' => true
        ]);
        return new DesignResource($design);
    }
}
