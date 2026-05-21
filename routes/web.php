<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageReactionController;
use App\Http\Controllers\UserStatusController;




Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('groups.index'));
    // Groups
    Route::resource('groups', GroupController::class)
         ->only(['index', 'store', 'show', 'destroy']);
    // Members
    Route::post('groups/{group}/members', [GroupMemberController::class, 'store'])
         ->name('groups.members.store');
    Route::delete('groups/{group}/members/{user}', [GroupMemberController::class, 'destroy'])
         ->name('groups.members.destroy');
    Route::patch('groups/{group}/members/{user}/promote', [GroupMemberController::class, 'promote'])
         ->name('groups.members.promote');
    
    // Messages
    Route::post('groups/{group}/messages', [MessageController::class, 'store'])
         ->name('messages.store');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])
         ->name('messages.destroy');
    Route::post('messages/{message}/read', [MessageController::class, 'markRead'])
         ->name('messages.read');

    // Reactions
    // Reactions
    Route::post('messages/{message}/reactions', [MessageReactionController::class, 'toggle'])
         ->name('reactions.toggle');

    // Status
    Route::patch('user/status', [UserStatusController::class, 'update'])
         ->name('user.status.update');

     // Typing Indicator
     Route::post('groups/{group}/messages/typing', [MessageController::class, 'typing'])
     ->name('messages.typing');
});

require __DIR__.'/auth.php';
