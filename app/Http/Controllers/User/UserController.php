<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Rules\MatchPassword;
use App\Rules\OldPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('designs')->all();
        return UserResource::collection($users);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'tagline' => ['required'],
            'about' => ['required', 'string', 'min:20'],
            'formatted_address' => ['required'],
            'location.latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'location.longitude' => ['required', 'numeric', 'min:-180', 'max:180']
        ]);
        //$location = new Point ($request->location['latitude'], $request->location['longitude']);
        $request->user()->update([
            'about' => $request->about,
            'tagline' => $request->tagline,
            'location.latitude' => $request->location['latitude'],
            'location.longitude' => $request->location['longitude'],
            'available_to_hire' => $request->available_to_hire,
            'formatted_address' => $request->formatted_address
        ]);
        return new UserResource($user);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'old_password' => ['required', new OldPassword()],
            'password' => ['required', 'confirmed', 'min:6', new MatchPassword()]
        ]);
        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);
        return response()->json(['message' => 'Password updated.'], 200);
    }

    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,bmp', 'max:2048']
        ]);
        $avatar = $request->file('avatar');
        $filename = time() . '_' . preg_replace('/\s+/', '_', $avatar->getClientOriginalName());
        if (Storage::disk('public')->exists('avatars/' . $user->id . '/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->id . '/' . $user->avatar);
        }
        $avatar->storeAs('avatars/' . $user->id, $filename, 'public');
        $request->user()->update([
            'avatar' => $filename
        ]);
        return new UserResource($user);
    }

    public function uploadCover(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'cover_image' => ['required', 'image', 'mimes:jpeg,jpg,png,bmp', 'max:2048']
        ]);
        $cover_image = $request->file('cover_image');
        $filename = time() . '_' . preg_replace('/\s+/', '_', $cover_image->getClientOriginalName());
        if (Storage::disk('public')->exists('covers/' . $user->id . '/' . $user->cover_image)) {
            Storage::disk('public')->delete('covers/' . $user->id . '/' . $user->cover_image);
        }
        $cover_image->storeAs('covers/' . $user->id, $filename, 'public');
        $request->user()->update([
            'cover_image' => $filename
        ]);
        return new UserResource($user);
    }

    public function getUserDesigns($id)
    {
        $user = User::with(['designs', 'comments'])->findOrFail($id);
        return DesignResource::collection($user->designs);
    }

    public function search(Request $request)
    {
        $query = (new User())->newQuery();
        //$user = User::query();
        //dd($user);
        //exit();
        // search for only has designs
        if ($request->has_designs) {
            $query->has('designs');
        }
        // check for available_to_hire
        if ($request->available_to_hire) {
            $query->where('available_to_hire', true);
        }
        // geagraphique search
        /*$lat = $request->latitude;
        $lng = $request->longitude;
        $dist = $request->distance;
        $unit = $request->unit;
        if($lat && $lng){
            $point = new Point($lat, $lng);
            $unit == 'km' ? $dist*=1000 : $dist*=1609.34;
            $query->distanceSphereExcludingSelf('location', $point, $dist);
        }

        // oeder the results
        if($request->orderBy == 'closest'){
            $query->orderByDistanceSphere('location', $point,'asc');
        }else*/
        if ($request->orderBy == 'latest') {
            $query->latest();
        } else {
            $query->oldest();
        }

        $designers = $query->get();
        return UserResource::collection($designers);
    }

    public function findByUsername($username)
    {
        $user = User::where('username', $username)->first();
        return new UserResource($user);
    }
}
