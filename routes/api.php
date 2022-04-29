<?php

use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\Auth\ForgotPassword;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPassword;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Design\CommentController;
use App\Http\Controllers\Design\DesignController;
use App\Http\Controllers\Design\UploadController;
use App\Http\Controllers\Team\InvitationController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\User\UserController;
use App\Http\Resources\UserResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user/me', function (Request $request) {
    return new UserResource($request->user());
});
// routes for publics
//designs
Route::get('/designs', [DesignController::class, 'index']);
Route::get('/designs/{id}', [DesignController::class, 'show']);
Route::get('/designs/slug/{slug}', [DesignController::class, 'findBySlug']);
Route::get('/design/{id}/liked', [DesignController::class, 'isLikedByUser']);
//users
Route::get('/users', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'getUserDesigns']);
Route::get('/user/{username}', [UserController::class, 'findByUserName']);
//teams
Route::get('/team/{id}', [TeamController::class, 'findById']);
// search
Route::get('/search/designs', [DesignController::class, 'search']);
Route::get('/search/designers', [UserController::class, 'search']);

// routes for guests
Route::middleware('guest:sanctum')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/email/verify/{user}', [EmailController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailController::class, 'resend']);
    Route::post('/forgot-password', [ForgotPassword::class, 'send'])->name('password.email');
    Route::post('/reset-password', [ResetPassword::class, 'reset'])->name('password.reset');
});

// routes for auth
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::post('/design/upload', [UploadController::class, 'upload']);
    Route::put('/design/{id}', [DesignController::class, 'update']);
    Route::delete('/design/{id}', [DesignController::class, 'destroy']);
    Route::post('/design/{id}/like', [DesignController::class, 'likehandler']);
    Route::post('/design/{id}/comment', [CommentController::class, 'store']);
    Route::put('/comment/{id}', [CommentController::class, 'edit']);
    Route::delete('/comment/{id}', [CommentController::class, 'destroy']);

    Route::put('/user/settings', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    Route::post('/user/cover-image', [UserController::class, 'uploadCover']);

    Route::post('/team', [TeamController::class, 'create']);
    Route::put('/team/{id}', [TeamController::class, 'update']);
    Route::get('/team/{id}/designs', [TeamController::class, 'teamDesigns']);
    Route::get('/users/teams', [TeamController::class, 'fetchUserTeams']);
    Route::delete('/team/{id}/user/{userId}', [TeamController::class, 'removeUserFromTeam']);

    Route::post('/invitation/team/{id}', [InvitationController::class, 'invite']);
    Route::post('/invitation-resend/{id}', [InvitationController::class, 'resend']);
    Route::post('/invitation-respond/{id}', [InvitationController::class, 'respond']);
    Route::delete('/invitation/{id}', [InvitationController::class, 'destroy']);

    Route::post('/chat', [ChatController::class, 'sendMessage']);
    Route::get('/chats-user', [ChatController::class, 'getUserChats']);
    Route::get('/chat-messages/{id}', [ChatController::class, 'getChatMessages']);
    Route::put('/chat-read/{id}', [ChatController::class, 'markAsRead']);
    Route::delete('/chat/{id}', [ChatController::class, 'destroyMessage']);
});
