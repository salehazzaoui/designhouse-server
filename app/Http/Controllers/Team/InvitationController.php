<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Mail\SendInvitationToJoinTeam;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    public function invite($teamId, Request $request)
    {
        $team = Team::findOrFail($teamId);
        $request->validate([
            'email' => ['required', 'email']
        ]);
        $user = $request->user();
        if (!$user->isOwnerOfTeam($team)) {
            return response()->json(['message' => 'you are not owner of this team.'], 401);
        }
        if ($team->hasPendingInvitation($request->email)) {
            return response()->json(['message' => 'email already invited.'], 401);
        }
        $recipient = User::where('email', $request->email)->first();
        if (!$recipient) {
            $this->createInvitation(false, $team, $request->email);
            return response()->json([
                'message' => 'invitation sent successfully.'
            ], 200);
        }
        // chek if the team already has user
        if ($team->hasUser($recipient)) {
            return response()->json([
                'email' => 'this user seems to be member of team already'
            ], 422);
        }
        // send the invitation to user
        $this->createInvitation(true, $team, $request->email);
        return response()->json([
            'message' => 'Invitation sent to user successfuly'
        ], 200);
    }

    public function resend($id, Request $request)
    {
        $invitation = Invitation::findOrFail($id);
        if (!$request->user()->isOwnerOfteam($invitation->team)) {
            return response()->json([
                'message' => 'you are nit the owner of this team'
            ], 401);
        }
        $recipient = User::where('email', $invitation->recipient_email)->first();
        Mail::to($invitation->recipient_email)->send(new SendInvitationToJoinTeam($invitation, !is_null($recipient)));
        return response()->json([
            'message' => 'Invitation resent to user successfuly'
        ], 200);
    }

    public function respond($id, Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'decision' => ['required']
        ]);
        $token = $request->token;
        $decision = $request->decision;
        $invitation = Invitation::findOrFail($id);
        //check if invitation belonge to user
        if ($invitation->recipient_email !== $request->user()->email) {
            return response()->json([
                'message' => 'this is not yours.'
            ], 401);
        }
        //check if token match
        if ($invitation->token !== $token) {
            return response()->json([
                'message' => 'Invalide token.'
            ], 401);
        }
        //check if accepted
        if ($decision !== 'deny') {
            $invitation->team->membres()->attach(auth()->id());
        }
        $invitation->delete();
        return response()->json(['message' => 'Successful.'], 200);
    }

    public function destroy($id)
    {
        $invitation = Invitation::findOrFail($id);
        $this->authorize('delete', $invitation);
        $invitation->delete();
        return response()->json(['message' => 'Invitation deleted.'], 200);
    }

    protected function createInvitation(bool $user_exists, Team $team, string $email)
    {
        $invitation = $team->invitations()->create([
            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime()))
        ]);
        Mail::to($email)->send(new SendInvitationToJoinTeam($invitation, $user_exists));
    }
}
