<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Logout;
use App\Models\Task_user_status;
use App\Models\Task;

class LogoutUserStatusListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $userId = $event->user->id; // Get the ID of the user logging out

        $task = Task_user_status::where('user_id', $userId)
            ->whereIn('user_status', ['Idle', 'Active'])
            ->latest()
            ->first();

        if ($task) {
            // Update task status to "Away"
            Task::where('id', $task->task_id)->update(['user_status' => 'Away']);

            // Insert new record in Task_user_status
            Task_user_status::create([
                'task_id' => $task->task_id,
                'user_id' => $userId,
                'user_status' => 'Away',
            ]);
        }
    }
}
