<?php

namespace App\Http\Controllers\Design;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function index()
    {
        $designs = Design::with('comments')->where('is_live', true)->get();
        return DesignResource::collection($designs);
    }

    public function show($id)
    {
        $design = Design::findOrFail($id);
        return new DesignResource($design);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'string', 'unique:designs,title,' . $id],
            'slug' => ['string'],
            'description' => ['required', 'string', 'max:140'],
        ]);
        $design = Design::find($id);
        $this->authorize('update', $design);
        $design->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'is_live' => $design->upload_successful ? true : false
        ]);

        return response()->json([
            'massage' => 'design updated.'
        ], 200);
    }

    public function destroy($id)
    {
        $design = Design::find($id);
        $this->authorize('delete', $design);
        foreach (['large', 'original', 'thumbnail'] as $size) {
            if (Storage::disk('public')->exists('designs/' . $size . '/' . $design->image)) {
                Storage::disk('public')->delete('designs/' . $size . '/' . $design->image);
            }
        }
        $design->delete();

        return response()->json([
            'massage' => 'design deleted.'
        ], 200);
    }

    public function likehandler($designId)
    {
        $design = Design::find($designId);
        if ($design->isLikeByUser(auth()->user()->id)) {
            $design->unlike();
        } else {
            $design->like();
        }
        return response()->json([
            'massage' => 'done.',
            'total' => $design->likes()->count()
        ], 200);
    }
    public function isLikedbyUser($designId)
    {
        $design = Design::find($designId);
        $isLiked = $design->isLikeByUser(auth()->id());
        return response()->json([
            'user_like' => $isLiked
        ], 200);
    }

    public function findBySlug($slug)
    {
        $design = Design::where('slug', $slug)->first();
        return new DesignResource($design);
    }

    public function search(Request $request)
    {
        $query = (new Design())->newQuery();
        $query->where('is_live', true);

        // return only designs with comment
        if ($request->has_comments) {
            $query->has('comments');
        }
        // return only designs assigned to team
        if ($request->has_team) {
            $query->has('team');
        }
        // search for title or description
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        // order the query by the like or latest first
        if ($request->orderBy == 'likes') {
            $query->withCount('likes')->orderByDesc('likes_count');
        } else {
            $query->latest();
        }
        $designs = $query->get();
        return DesignResource::collection($designs);
    }
}
