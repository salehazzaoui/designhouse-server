<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:teams'],
        ]);
        $user = User::find(auth()->id());
        $team = $user->teams()->create([
            'owner_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'unique:teams'],
        ]);
        $team = Team::findOrFail($id);
        $this->authorize('update', $team);
        $team->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    public function findById($id)
    {
        $team = Team::with(['membres', 'designs'])->findOrFail($id);
        return new TeamResource($team);
    }

    public function fetchUserTeams(Request $request)
    {
        $teams = $request->user()->teams()->get();
        return TeamResource::collection($teams);
    }

    public function removeUserFromTeam(Request  $request, $teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        $user = User::findOrFail($userId);
        if ($team->isOwnerOfTeam($userId)) {
            return response()->json(['message' => 'you are not owner team.'], 401);
        }
        if (!$request->user()->isOwnerOfTeam($userId) && auth()->id() !== $user->id) {
            return response()->json(['message' => 'you can not do this.'], 401);
        }
        $team->membres()->detach($user->id);
        return response()->json(['message' => $user->name . 'removed from your team.'], 200);
    }

    public function teamDesigns($id)
    {
        $team = Team::findOrFail($id);
        $designs = $team->designs()->get();
        return DesignResource::collection($designs);
    }
}
