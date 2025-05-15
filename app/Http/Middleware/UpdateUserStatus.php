<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Task_user_status;
use App\Models\Task;

class UpdateUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()){
            $task = Task_user_status::where('user_id', Auth::id())
            ->whereIn('user_status', ['Idle', 'Active'])
            ->latest()
            ->first();

            if ($task) {
                // Update task status to "Away"
                Task::where('id', $task->task_id)->update(['user_status' => 'Away']);

                // Insert new record in Task_user_status
                Task_user_status::create([
                    'task_id' => $task->task_id,
                    'user_id' => Auth::id(),
                    'user_status' => 'Away',
                ]);
            }
        }
        return $next($request);
    }
}
