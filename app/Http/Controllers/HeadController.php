<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Log;
use App\Models\Department;
use App\Models\Member;
use App\Models\Task_templates;
use App\Models\Task_group;
use App\Models\Task_tempo_group;
use App\Models\Link_tempo_group;
use App\Models\Task_solo;
use App\Models\Task_solo_auto;
use App\Models\Task_group_auto;
use App\Models\Task_group_member_auto;
use App\Models\Task_group_tempo_member_auto;
use App\Models\Link_solo_auto;
use App\Models\Link_group_auto;
use App\Models\Link_member_group_auto;
use App\Models\Link_tempo_group_auto;
use App\Models\Task;
use App\Models\Task_fields;
use App\Models\Task_inputs;
use App\Models\Task_pages;
use App\Models\Task_templates_pages;
use App\Models\Task_templates_fields;
use App\Models\Notification;
use App\Models\Archive_templates;
use App\Models\Archive_templates_fields;
use App\Models\Archive_templates_pages;
use App\Models\Task_submit_data;
use App\Models\Task_check_auto;
use App\Models\Task_distribute_auto;
use App\Models\Task_template_distribute_auto;
use App\Models\Task_distribute_auto_accept;
use App\Models\Archive_task;
use App\Models\Archive_task_pages;
use App\Models\Archive_task_inputs;
use App\Models\Archive_task_fields;
use App\Models\Archive_task_solo;
use App\Models\Archive_task_group;
use App\Models\Task_user_status;
use App\Models\Feedback;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\EditMessage;
use App\Models\Reactions;
use App\Models\PinnedMessage;
use App\Models\SeenMessage;
use App\Models\Meeting;
use App\Models\CalendarEvent;
use App\Models\PersonalTask;
use App\Models\NotesTask;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use LDAP\Result;
use App\Mail\PHPMailerService;
use Illuminate\Support\Facades\Session;
use App\Providers\RouteServiceProvider;
use App\Services\PhilSMSService;
use Illuminate\Validation\Rules\Password as PasswordRule;
use DateTime;

class HeadController extends Controller
{
    protected $smsService;

    public function __construct(PhilSMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function HeadDashboardGetAllChats(){
        $userId = Auth::id();
        $chats = ChatParticipant::where('chat_participants.user_id', $userId)
        ->join('chats', 'chats.id', '=', 'chat_participants.chat_id')
        ->leftJoin('messages', function ($join) {
            $join->on('messages.chat_id', '=', 'chats.id')
                 ->whereRaw('messages.created_at = (SELECT MAX(created_at) FROM messages WHERE messages.chat_id = chats.id)');
        })
        ->select('chat_participants.chat_id', 'chats.type', 'chats.photo', 'chats.name', 'messages.created_at as last_message_time')
        ->orderBy('messages.created_at', 'desc') // Sort by last message time
        ->get();

        // Process each chat
        $chatResult = [];
        foreach ($chats as $chat) {
            $chatId = $chat->chat_id;
            $chatType = $chat->type;
            $chatName = $chat->name;
            $chatPhoto = '';
            $convoPhoto = $chat->photo;
            $isOnline = false;

            if ($chatType === 'user_to_user') {
                // For user-to-user chats, get the other participant's name, photo, and online status
                $otherParticipant = ChatParticipant::where('chat_id', $chatId)
                    ->where('user_id', '!=', $userId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->first(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $chatName = !empty($chat->name) ? $chat->name : ($otherParticipant->nickname ?: ($otherParticipant->name ?? 'Unknown User'));
                $chatPhoto = $otherParticipant->photo; // Default photo if none
                $isOnline = $otherParticipant->is_online ?? false; // Default to false if none
            } elseif ($chatType === 'group') {
                // For group chats with no name, get all participants' names, photos, and online status
                $participants = ChatParticipant::where('chat_id', $chatId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->get(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $participantNames = $participants->map(function ($participant) {
                    return $participant->nickname ?: ($participant->name ?? 'Unknown User');
                })->toArray();

                $chatName = !empty($chat->name) ? $chat->name : implode(', ', $participantNames);

                // Get the first participant's photo as the group chat photo (or use a default)
                $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
                $chatPhoto = $participantPhotos;

                // Check if any participant is online
                $isOnline = $participants->contains('is_online', true);
            }

            // Get the count of messages not seen by the user
            $unseenCount = Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $userId) // Messages sent by others
                ->whereIn('status', ['sent', 'delivered']) // Not seen yet
                ->count();

            // Get the last message in the chat
            $lastMessage = Message::where('chat_id', $chatId)
                ->orderBy('created_at', 'desc')
                ->first(['message', 'created_at', 'id', 'is_unsend']);

            $hasImageAttachment = true;
            if ($lastMessage) {
                $hasImageAttachment = MessageAttachment::where('message_id', $lastMessage->id)
                    ->exists(); // Check if an image attachment exists
            }

            $lastMessageFrom = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->first(['user_id']);

            // Format the last message time using Carbon
            $lastMessageTime = $lastMessage
                ? Carbon::parse($lastMessage->created_at)->diffForHumans() // e.g., "1 sec ago"
                : null;

            // Add the chat to the result
            $chatResult[] = [
                'chat_id' => $chatId,
                'type' => $chatType,
                'name' => $chatName,
                'convo_photo' => $convoPhoto,
                'photo' => $chatPhoto, // Photo of the other participant or group
                'is_online' => $isOnline, // Online status of the other participant or group
                'unseen_count' => $unseenCount, // Number of unseen messages
                'last_message' => $lastMessage ? $lastMessage->message : null, // Last message text
                'from_message' => $lastMessageFrom->user_id,
                'is_attached' => $hasImageAttachment,
                'last_message_time' => $lastMessageTime, // Last message time in "time ago" format
                'last_message_actual_time' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                'is_unsend' => $lastMessage->is_unsend,
                'auth_id' => Auth::id()
            ];
        }

        $chatConvo = [];

        foreach($chatResult as $getChat){
            $chat_id = $getChat['chat_id'];
            $page = $request->page ?? 1; // Get page number from request
            $perPage = 20; // Messages per page

            $chat = Chat::find($chat_id);
            if(!$chat){
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! This conversation is not existing, it might be deleted please check again."
                ]);
            }

            $convoPhoto = $chat->photo;

            $chatPart = ChatParticipant::where('chat_id', $chat_id)->where('user_id', Auth::id())->first();
            if(!$chatPart){
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! You can't access this conversation."
                ]);
            } else {
                ChatParticipant::where('user_id', Auth::id())->where('chat_id', '!=', $chat_id)->where('is_here', 1)->update(['is_here' => 0]);
                $chatPart->is_here = 1;
                $chatPart->save();
            }

            $otherPart = [];
            if ($chat->type === 'user_to_user') {
                // For user-to-user chats, get the other participant's name, photo, and online status
                $otherParticipant = ChatParticipant::where('chat_id', $chat_id)
                    ->where('user_id', '!=', Auth::id())
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->first(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $chatName = !empty($chat->name) ? $chat->name : ($otherParticipant->nickname ?: ($otherParticipant->name ?? 'Unknown User'));
                $chatPhoto = $otherParticipant->photo; // Default photo if none
                $isOnline = $otherParticipant->is_online ?? false; // Default to false if none

                $otherPart[] = [
                    'other_name' => $chatName,
                    'other_photo' => $chatPhoto,
                    'other_online' => $isOnline,
                    'type' => $chat->type
                ];
            } elseif ($chat->type === 'group') {
                // For group chats with no name, get all participants' names, photos, and online status
                $participants = ChatParticipant::where('chat_id', $chat_id)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->get(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                // Prioritize nickname, then name, then "Unknown User"
                $participantNames = $participants->map(function ($participant) {
                    return $participant->nickname ?: ($participant->name ?? 'Unknown User');
                })->toArray();

                // Get the first participant's photo as the group chat photo (or use a default)
                $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
                $chatPhoto = $participantPhotos;

                // Check if any participant is online
                $isOnline = $participants->contains('is_online', true);

                $otherPart[] = [
                    'other_name' => !empty($chat->name) ? $chat->name : (implode(', ', $participantNames)), // Join names for display
                    'other_photo' => $chatPhoto,
                    'other_online' => $isOnline,
                    'type' => $chat->type
                ];
            }

            $pinnedMessages = [];
            $getPinned = PinnedMessage::where('chat_id', $chat_id)
            ->orderBy('id', 'desc') // Assuming 'id' is incremental
            ->first();
            if($getPinned){
                $pinVal = Message::find($getPinned->message_id);
                $pinnedUser = User::find($pinVal->user_id);

                $chatParticipant = ChatParticipant::where('chat_id', $chat_id)
                    ->where('user_id', $pinVal->user_id)
                    ->first();

                $userName = $chatParticipant && !empty($chatParticipant->nickname)
                    ? $chatParticipant->nickname
                    : ($pinnedUser->name ?? 'Unknown');

                $pinnedTime = Carbon::parse($getPinned->created_at);
                if ($pinnedTime->isToday('Asia/Manila')) {
                    $formattedpinnedTime = $pinnedTime->format('h:i A'); // e.g., "02:30 PM"
                } else {
                    $formattedpinnedTime = $pinnedTime->format('h:i A, M d'); // e.g., "02:30 PM, Aug 10"
                }

                $pinnedMessages[] = [
                    'chat_id' => $chat_id,
                    'message_id' => $pinVal->id,
                    'user_id' => $pinVal->user_id,
                    'message' =>  $pinVal->message,
                    'created_at' => $formattedpinnedTime,
                    'photo' => $pinnedUser->photo ?? null,
                    'user_name' => $userName
                ];
            }

            $currentUserId = Auth::id();

            // Retrieve message IDs that need to be marked as seen
            $messageIds = Message::where('chat_id', $chat_id)
                ->where('user_id', '!=', $currentUserId)
                ->whereIn('status', ['sent', 'delivered', 'seen'])
                ->pluck('id');

            if ($messageIds->isNotEmpty()) {
                // Update status for relevant messages
                Message::whereIn('id', $messageIds)
                    ->whereIn('status', ['sent', 'delivered'])
                    ->update(['status' => 'seen']);

                // Get existing seen records
                $existingSeen = SeenMessage::where('user_id', $currentUserId)
                    ->whereIn('message_id', $messageIds)
                    ->pluck('message_id')
                    ->toArray();

                // Find new message IDs that need to be marked as seen
                $newMessageIds = array_diff($messageIds->toArray(), $existingSeen);

                if (!empty($newMessageIds)) {
                    $now = Carbon::now();

                    // Bulk insert with timestamps
                    $seenData = array_map(function ($messageId) use ($currentUserId, $now) {
                        return [
                            'message_id' => $messageId,
                            'user_id' => $currentUserId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $newMessageIds);

                    // Use transaction for atomic operations
                    DB::transaction(function () use ($seenData, $newMessageIds, $now) {
                        // Insert new seen records
                        SeenMessage::insertOrIgnore($seenData);

                        // Update messages' updated_at
                        Message::whereIn('id', $newMessageIds)
                            ->update(['updated_at' => $now]);
                    });
                }
            }

            $messageQuery = Message::where('chat_id', $chat_id)
            ->orderBy('created_at', 'desc'); // Important: order by date descending

            $totalMessages = $messageQuery->count();
            $messages = $messageQuery->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->reverse(); // Reverse to maintain chronological order

            $lastMessageIdInEntireChat = Message::where('chat_id', $chat_id)
                ->orderBy('created_at', 'desc')
                ->value('id');

            $formattedMessages = [];
            if(!empty($messages)){
                foreach($messages as $val){
                    $formattedMessageTime = null;
                    $messageId = $val->id;
                    $messageUser = $val->user_id;
                    $messageContent = $val->message;
                    $messageStatus = $val->status;
                    $messageLastSeen = $val->last_seen;
                    $messageTime = Carbon::parse($val->created_at);
                    if ($messageTime->isToday('Asia/Manila')) {
                        $formattedMessageTime = $messageTime->format('h:i A'); // e.g., "02:30 PM"
                    } else {
                        $formattedMessageTime = $messageTime->format('h:i A, M d'); // e.g., "02:30 PM, Aug 10"
                    }

                    $reactions = Reactions::where('message_id', $messageId)->with('user')->get();
                    $chatter = User::find($messageUser);

                    $chatParticipant = ChatParticipant::where('chat_id', $chat_id)
                        ->where('user_id', $messageUser)
                        ->first();

                    $userName = $chatParticipant && !empty($chatParticipant->nickname)
                        ? $chatParticipant->nickname
                        : ($chatter->name ?? 'Unknown');

                    $formattedMessages[] = [
                        'chat_id' => $chat_id,
                        'replied_id' => $val->replied_id,
                        'task_id' => $val->task_id,
                        'message_id' => $messageId,
                        'user_id' => $messageUser,
                        'message' =>  $messageContent, // Photo of the other participant or group
                        'status' => $messageStatus, // Online status of the other participant or group
                        'last_seen' => $messageLastSeen, // Number of unseen messages
                        'created_at' => $formattedMessageTime,
                        'photo' => $chatter->photo,
                        'my_id' => Auth::id(),
                        'is_edited' => $val->is_edited,
                        'is_forwarded' => $val->is_forwarded,
                        'is_unsend' => $val->is_unsend,
                        'is_pinned' => $val->is_pinned,
                        'user_name' => $userName,
                        'reactions' => $reactions,
                        'last_message_id' =>  $lastMessageIdInEntireChat
                    ];
                }
            }

            $convoAttachments = Message::with('attachments')
                ->where('chat_id', $chat_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $attachmentData = [];
            if ($convoAttachments->isNotEmpty()){
                foreach ($convoAttachments as $convo) {
                    // Attachments are already loaded, no need for additional query
                    foreach ($convo->attachments as $attachment) {
                        $filePath = $attachment->file_path;
                        $fileName = basename($filePath);
                        $parts = explode('_', $fileName, 2);
                        $originalNameWithExt = count($parts) === 2 ? $parts[1] : $fileName;

                        $attachmentData[] = [
                            'id' => $attachment->id,
                            'path' => $filePath,
                            'type' => $attachment->file_type,
                            'name' => $originalNameWithExt,
                            'message_id' => $convo->id,
                            'created_at' => $attachment->created_at
                        ];
                    }
                }
            }

            $customNickname = ChatParticipant::where('chat_id', $chat_id)
            ->join('users', 'users.id', '=', 'chat_participants.user_id')
            ->get(['chat_participants.user_id', 'chat_participants.nickname', 'chat_participants.chat_id', 'chat_participants.is_admin', 'chat_participants.is_creator', 'users.name', 'users.photo', 'users.is_online']);
            $authId = Auth::id(); // Get the logged-in user ID

            $imAdmin = ChatParticipant::where('chat_id', $chat_id)
                ->where('user_id', $authId)
                ->where(function ($query) {
                    $query->where('is_admin', 1)
                        ->orWhere('is_creator', 1);
                })
                ->exists(); // Returns true if user is an admin/creator

            // Convert boolean to integer (1 = admin, 0 = not admin)
            $imAdmin = $imAdmin ? 1 : 0;
            foreach ($customNickname as $user) {
                $user->im_admin = $imAdmin;
                $user->im_user = ($user->user_id === $authId) ? 1 : 0;
            }

            $toAddMember = User::leftJoin('chat_participants', function($join) use ($chat_id) {
                    $join->on('chat_participants.user_id', '=', 'users.id')
                        ->where('chat_participants.chat_id', $chat_id);
                })
                ->whereNull('chat_participants.user_id') // Users who are NOT in the chat
                ->get(['users.*']);

            $chatConvo[] = [
                'chat_id' => $chat_id,
                'messages' => $formattedMessages,
                'otherPart' => $otherPart,
                'hasMore' => ($page * $perPage) < $totalMessages, // Indicate if more messages exist
                'currentPage' => $page,
                'totalMessages' => $totalMessages,
                'pinnedMessages' => $pinnedMessages,
                'chat_info' => $chat,
                'convoAttachments' => $attachmentData,
                'customNickname' => $customNickname,
                'isMuted' => $chatPart,
                'convo_photo' => $convoPhoto,
                'toAddMember' => $toAddMember
            ];
        }

        return response()->json([
            'status' => 'exist',
            'chats' => $chatResult,
            'convos' =>  $chatConvo
        ]);
    }

    public function HeadDashboardGetAllNotification(Request $request){
        $lastId = $request->input('last_id', 0);
        $query = Notification::where('user_id', Auth::id())
                         ->orderBy('created_at', 'desc');

        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $notif = $query->get();

        foreach($notif as $row){
            $row->timeago = Carbon::parse($row->created_at)->diffForHumans() ;
        }

        $hasUnread = Notification::where('user_id', Auth::id())
                             ->where('is_read', 0)
                             ->exists();

        return response()->json([
            'status' => 'exist',
            'notif' => $notif,
            'has_unread' => $hasUnread,
        ]);
    }

    public function HeadDashboardMarkAsReadedNotification(Request $request){
        Notification::where('user_id', Auth::id())->where('is_read', 0)->update([
            'is_read' => $request->read
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function HeadDashboardClearNotification(Request $request){
        Notification::where('user_id', Auth::id())->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function HeadDashboard(){
        $timezone = "Asia/Manila";

        $PerDepartment = DB::table(DB::raw("
            (SELECT
                users.id AS user_id,
                users.name,
                users.username,
                users.photo,
                departments.name AS department_name,
                departments.id AS department_id,
                COUNT(table_task_submit_data.id) AS task_count,
                ROW_NUMBER() OVER (PARTITION BY departments.id ORDER BY COUNT(table_task_submit_data.id) DESC) AS rank
            FROM table_task_submit_data
            INNER JOIN users ON table_task_submit_data.user_id = users.id
            INNER JOIN departments ON table_task_submit_data.department_id = departments.id
            WHERE MONTH(table_task_submit_data.created_at) = " . Carbon::now($timezone)->month . "
            GROUP BY users.id, users.name, users.username, users.photo, departments.id, departments.name
            ) AS ranked_data
        "))
        ->where('rank', 1)
        ->get();

        $internCount = User::where('role', 'intern')->count();
        $employeeCount = User::where('role', 'employee')->count();
        $observerCount = User::where('role', 'observer')->count();
        $total = User::count();

        $temp = Task_templates::all();

        $ongoing = Task::whereIn('status', ['Ongoing', 'Overdue'])->get();
        $tocheck = Task::where('status', 'To Check')->get();
        $complete = Task::where('status', 'Completed')->get();
        $dist = Task::where('status', 'Distributed')->get();
        $linked = Task::where('status', 'Linked')->get();

        return view('head.index', [
            'internCount' => $internCount ?? 0,
            'employeeCount' => $employeeCount ?? 0,
            'observerCount' => $observerCount ?? 0,
            'total' => $total ?? 0,
            'PerDepartment' => $PerDepartment,
            'temp' => $temp,
            'ongoing' => $ongoing,
            'tocheck' => $tocheck,
            'complete' => $complete,
            'dist' => $dist,
            'linked' => $linked,
        ]);
    }

    public function reloadMyOngoingDiv(Request $request){
        // Retrieve all tasks from the database.
        $tasks = Task::whereIn('status', ['Ongoing', 'Overdue'])->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $task = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastUpdate' => $currentSnapshot,
                    'task'       => $updatedTask // Return only the updated task
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function HeadLogin(){
        if (Auth::check()) {
            return redirect('/head/dashboard'); // Redirect if logged in
        }
        return view('head.head_login');
    }

    public function HeadLogout(Request $request){
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/head/login');
    }

    public function HeadForgot(){
        if (Auth::check()) {
            return redirect('/head/dashboard'); // Redirect if logged in
        }

        return view('head.head_forgot');
    }

    public function HeadSendOtpEmail(Request $request){
        if(isset($request->otp)){
            $otp = $request->otp;
            $user_id = $request->user_id_email;

            $compare = Otp::where('user_id', $user_id)->first();
            if(intval($compare->otp_code) === intval($otp)){
                Otp::where('user_id', $user_id)->delete();
                return response()->json([
                    'status' => 'success',
                    'user_id' => $user_id
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect OTP code please try again'
                ]);
            }
        }
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'This '.$email.' is not existing in the database please try again'
            ]);
        }

        $otpCode = rand(100000, 999999);
        Otp::where('user_id', $user->id)->delete();

        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode
        ]);
        $subject = "Your OTP for Password Reset";
        $body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Secure OTP Verification | Tribo Corporation</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                body {
                    font-family: 'Poppins', Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                    color: #444;
                }
                .email-wrapper {
                    max-width: 640px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                .email-header {
                    background: #4C52E4;
                    padding: 30px 20px;
                    text-align: center;
                    border-bottom: 4px solid #3a40c9;
                }
                .email-header h1 {
                    color: #fff;
                    font-size: 24px;
                    font-weight: 600;
                    margin: 0;
                    letter-spacing: 0.5px;
                }
                .email-content {
                    padding: 40px;
                }
                .otp-container {
                    background: #f5f6ff;
                    border-radius: 8px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: center;
                    border: 1px dashed #4C52E4;
                }
                .otp-code {
                    font-size: 42px;
                    font-weight: 700;
                    letter-spacing: 5px;
                    color: #4C52E4;
                    margin: 15px 0;
                }
                .validity-note {
                    font-size: 14px;
                    color: #666;
                    margin-top: 10px;
                }
                .cta-button {
                    display: inline-block;
                    background: #4C52E4;
                    color: #fff !important;
                    text-decoration: none;
                    padding: 12px 30px;
                    border-radius: 6px;
                    font-weight: 500;
                    margin: 20px 0;
                }
                .security-note {
                    background: #fff8f8;
                    border-left: 4px solid #ff4d4d;
                    padding: 15px;
                    margin: 25px 0;
                    font-size: 14px;
                    border-radius: 4px;
                }
                .footer {
                    background: #4C52E4;
                    padding: 20px;
                    text-align: center;
                    color: rgba(255,255,255,0.9);
                    font-size: 12px;
                }
                .company-info {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #666;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='email-header'>
                    <h1>SECURE PASSWORD RESET</h1>
                </div>

                <div class='email-content'>
                    <p>Dear {$user->name},</p>

                    <p>We've received a request to reset your Tribo Corporation account password. For your security, please use the following One-Time Password (OTP) to verify your identity:</p>

                    <div class='otp-container'>
                        <div style='font-size: 14px; color: #4C52E4; margin-bottom: 10px;'>YOUR VERIFICATION CODE</div>
                        <div class='otp-code'>{$otpCode}</div>
                        <div class='validity-note'>Valid for 5 minutes only</div>
                    </div>

                    <div class='security-note'>
                        <strong>Security Alert:</strong> Never share this code with anyone. Tribo Corporation will never ask you for your OTP or password.
                    </div>

                    <p>If you didn't request this password reset, please secure your account by changing your password immediately or contact our support team.</p>

                    <div class='company-info'>
                        <strong>Tribo Corporation</strong><br>
                        Email: tribo.corp@tribo.uno<br>
                        Support Hours: Mon-Fri, 9AM-6PM
                    </div>
                </div>

                <div class='footer'>
                    © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                    This is an automated message - please do not reply directly
                </div>
            </div>
        </body>
        </html>
        ";

        // Use the PHPMailerService to send the OTP email
        $mailer = new PHPMailerService();
        $sendResult = $mailer->sendMail($user->email, $user->name, $subject, $body);

        if ($sendResult == 'Message has been sent successfully') {
            return response()->json([
                'status' => 'success',
                'user_id' => $user->id
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'There was an error sending the OTP. Please try again later. message:  '.$sendResult
            ]);
        }
    }

    public function HeadSendOtpPhone(Request $request){
        if(isset($request->otp)){
            $otp = $request->otp;
            $user_id = $request->user_id_phone;

            $compare = Otp::where('user_id', $user_id)->first();

            if (!$compare) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP not found'
                ]);
            }

            // Check if OTP is older than 5 minutes
            if ($compare->created_at->diffInMinutes(now()) > 5) {
                // Delete expired OTP
                $compare->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP has expired. Please request a new one.'
                ]);
            }

            // Compare OTP codes
            if (intval($compare->otp_code) === intval($otp)) {
                $compare->delete();

                return response()->json([
                    'status' => 'success',
                    'user_id' => $user_id
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect OTP code. Please try again.'
                ]);
            }
        }
        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'This ' . $phone . ' is not existing in the database. Please try again.'
            ]);
        }

        $otpCode = mt_rand(100000, 999999); // 6-digit OTP

        $message = "Tribo Corp: Your one-time password (OTP) is {$otpCode}. It is valid for 5 minutes. Never share this code with anyone, including Tribo Corp staff.";
        Otp::where('user_id', $user->id)->delete();
        // Store OTP in the database
        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode
        ]);

        // Send SMS
        $smsService = new PhilSMSService();
        $response = $smsService->sendSMS($user->phone, $message);

        // Check if the response indicates success or failure
        if ($response['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'user_id' => $user->id,
                'message' => 'OTP sent successfully'
            ]);
        } else {
            // Log or return error message from the API
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $response['message']
            ]);
        }
    }

    public function HeadSubmitNewPass(Request $request){
        if(isset($request->redirect)){
            $user = User::where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->orWhere('phone', $request->login)
                    ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // If user exists and password is correct, log the user in
                Auth::login($user);

                // Mark user as online
                $user->is_online = true;
                $user->save();

                // Regenerate session to prevent session fixation
                $request->session()->regenerate();

                // Store last activity time
                Session::put('last_activity', now());

                return response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Authentication failed
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ]);
            }
        }

        if(isset($request->set)){
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found']);
            }

            // Update the user's password
            $user->password = Hash::make($request->new_pass);
            $user->save();

            $login = $user->email ?? $user->phone ?? $user->username ?? '';

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully',
                'login' => $login,
                'password' => $request->new_pass
            ]);
        }

    }

    public function HeadProfile(){
        $id = Auth::user()->id;
        $profile = User::find($id);

        return view('head.head_profile', compact('profile'));
    }

    public function SavePhoto(Request $request){
        $id = Auth::user()->id;
        $data = User::find($id);
        //$data->photo = $request->photo;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            $fileName = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/photo_bank'),$fileName);
            $data->photo = $fileName;
        }

        $data->save();

        return response()->json(['status' => 'success']);
    }

    public function ProfileInfo(Request $request){
        $id = Auth::user()->id;
        $data = User::find($id);

        if (!empty($request->email)) {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please enter a valid email address'
                ]);
            }

            $check = User::where('email', $request->email)
                       ->where('id', '!=', $id)
                       ->first();

            if ($check) {
                return response()->json(['status' => 'emailExist']);
            }
        }

        if (!empty($request->phone)) {
            // Validate Philippine phone number format
            if (!preg_match('/^09\d{9}$/', $request->phone)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Phone number must be a valid Philippine number starting with 09 (e.g. 09123456789)'
                ]);
            }

            // Check for duplicate phone numbers (excluding current user)
            $checkPhone = User::where('phone', $request->phone)
                             ->where('id', '!=', $id)
                             ->first();

            if ($checkPhone) {
                return response()->json(['status' => 'phoneExist']);
            }
        }

        $data->email = $request->email;
        $data->phone = $request->phone;

        $data->save();
        return response()->json(['status' => 'success']);
    }

    public function BasicInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'birthdate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    $birthdate = new DateTime($value);
                    $today = new DateTime();
                    $age = $today->diff($birthdate)->y;

                    if ($age < 18) {
                        $fail('You must be at least 18 years old.');
                    }
                    if ($age > 100) {
                        $fail('Maximum allowed age is 100 years.');
                    }
                }
            ],
            'address' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all()
            ]);
        }

        $id = Auth::user()->id;
        $data = User::find($id);

        $data->name = $request->name;
        $data->birthdate = $request->birthdate;
        $data->address = $request->address;

        $data->save();
        return response()->json(['status' => 'success']);
    }

    public function UpdatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'newPassword' => [
                'required',
                'string',
                'min:8',
                'different:password',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'confirmPassword' => ['required', 'string', 'same:newPassword'],
        ], [
            'newPassword.min' => 'Password must be at least 8 characters',
            'newPassword.different' => 'New password must be different from current password',
            'confirmPassword.same' => 'Passwords do not match'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all()
            ]);
        }

        $user = Auth::user();
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }

//region Task
    public function HeadTasks(){
        $department = Department::all();
        $temp = Task_templates::where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        foreach ($temp as $row) {
            // Query the department table based on the department_id
            $depart = Department::find($row->department_id);
            $row->department_name = $depart ? $depart->name : 'No Department';
        }

        $ongoing = Task::whereIn('status', ['Ongoing','Overdue'])->where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        $tocheck = Task::where('status', 'To Check')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        $complete = Task::where('status', 'Completed')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        $dist = Task::where('status', 'Distributed')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        $linked = Task::where('status', 'Linked')->get();

        $distReq = Task_distribute_auto::where('status', 'Pending')->count();
        $distReqView = Task_distribute_auto::where('status', 'Pending')->get();
        $department_dist = Department::all();
        $ongoingLink = Task::whereIn('status', ['Ongoing', 'Overdue'])->whereNull('link_id')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();
        $requestOvertime = Task::whereIn('status', ['Ongoing', 'Overdue'])
            ->where('user_status', 'Request Overtime')
            ->with(['solo.user', 'group.user'])
            ->get();
        $archived = Task::where('is_archived', 1)->get();
        $archivedTemp = Task_templates::where('is_archived', 1)->get();

        return view('head.head_tasks', compact('department', 'temp', 'ongoing', 'tocheck', 'complete', 'dist', 'distReq', 'distReqView', 'department_dist', 'ongoingLink', 'linked', 'requestOvertime', 'archived', 'archivedTemp'));
    }

    public function reloadOngoingDiv(Request $request){
        // Retrieve all tasks from the database.
        $tasks = Task::whereIn('status', ['Ongoing', 'Overdue'])->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastUpdate' => $currentSnapshot,
                    'task'       => $updatedTask // Return only the updated task
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function reloadToCheckDiv(Request $request){
        $tasks = Task::where('status', 'To Check')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastToCheckUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastToCheckUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastToCheckUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastToCheckUpdate' => $currentSnapshot,
                    'task'       => $updatedTask // Return only the updated task
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastToCheckUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function reloadCompleteDiv(Request $request){
        $tasks = Task::where('status', 'Completed')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastCompleteUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastCompleteUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastCompleteUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastCompleteUpdate' => $currentSnapshot,
                    'task'       => $updatedTask // Return only the updated task
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastCompleteUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function reloadDistributeDiv(Request $request){
        $tasks = Task::where('status', 'Distributed')->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastDistributeUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastDistributeUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastDistributeUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastDistributeUpdate' => $currentSnapshot,
                    'task'       => $updatedTask // Return only the updated task
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastDistributeUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function reloadDistributeReqDiv(Request $request){
        $tasks = Task_distribute_auto::where('status', 'Pending')->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastDistributeReqUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastDistributeReqUpdate' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task_distribute_auto::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastDistributeReqUpdate'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $id => $updatedAt) {
            // If the task is missing or the timestamp is different, we have an update.
            if (!isset($clientSnapshot[$id]) || $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task_distribute_auto::find($id);

                return response()->json([
                    'status'     => 'task_updated',
                    'lastDistributeReqUpdate' => $currentSnapshot,
                    'task'       => $updatedTask,
                    'count'      => $tasks->count()
                ]);
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastDistributeReqUpdate' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function reloadOvertimeReqDiv(Request $request){

        $tasks = Task::whereIn('status', ['Ongoing', 'Overdue'])
        ->where('user_status', 'Request Overtime')
        ->with(['solo.user', 'group.user'])
        ->get();

        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($tasks as $task) {
            $currentSnapshot[$task->id] = $task->updated_at->toDateTimeString();
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastOvertimeRequest, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastOvertimeRequest' => $currentSnapshot,
                'tasks'      => $tasks
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new tasks (tasks in $currentSnapshot but not in $clientSnapshot)
            $newTasks = [];
            foreach ($currentSnapshot as $id => $updatedAt) {
                if (!isset($clientSnapshot[$id])) {
                    $newTasks[] = Task::find($id);
                }
            }

            // Find deleted tasks (tasks in $clientSnapshot but not in $currentSnapshot)
            $deletedTaskIds = [];
            foreach ($clientSnapshot as $id => $updatedAt) {
                if (!isset($currentSnapshot[$id])) {
                    $deletedTaskIds[] = $id;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastOvertimeRequest'      => $currentSnapshot,
                'newTasks'        => $newTasks,       // New tasks to add
                'deletedTaskIds'  => $deletedTaskIds  // Task IDs to remove
            ]);
        }

        $updatedTasks = [];
        foreach ($currentSnapshot as $id => $updatedAt) {
            if (isset($clientSnapshot[$id]) && $clientSnapshot[$id] !== $updatedAt) {
                $updatedTask = Task::find($id);
                if ($updatedTask) {
                    $updatedTasks[] = $updatedTask;
                }
            }
        }

        if (!empty($updatedTasks)) {
            return response()->json([
                'status'     => 'task_updated',
                'lastOvertimeRequest' => $currentSnapshot,
                'tasks'      => $updatedTasks // Return updated tasks
            ]);
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastOvertimeRequest' => $currentSnapshot,
            'tasks'      => $tasks
        ]);
    }

    public function HeadTasksEditTemplate(){
        $pages = Task_templates_pages::where('template_id', $_GET['temp'])->get();

        $pagesWithContent = $pages->map(function ($page) {
            $content = Task_templates_fields::where('field_page', $page->id)->get();
            return [
                'pages' => $page,
                'contents' => $content,
            ];
        });

        $temp = Task_templates::find($_GET['temp']);

        return response()->json(['pagesWithContent' => $pagesWithContent, 'page' => $pages, 'stepper' => $temp]);
    }

    public function HeadTasksEditInfoTemplate(Request $request){
        $id = $request->id;
        $temp = Task_templates::find($id);

        return response()->json(['info' => $temp]);
    }

    public function HeadTasksAddPage(Request $request){
        $temp = Task_templates::find($request->temp_id);
        $temp->pages = $request->count;
        $temp->save();

        $temp_page = new Task_templates_pages();
        $temp_page->template_id = $request->temp_id;
        $temp_page->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Create Page in Template: {$request->temp_id}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksSavePageTitle(Request $request){
        $validator = Validator::make($request->all(), [
            'page_title' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $temp_page = Task_templates_pages::find($request->page_id);
        $temp_page->page_title = $request->page_title;
        $temp_page->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Set Page Title in Template: '{$temp_page->template_id}' as {$request->page_title}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksSaveTaskInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $temp = Task_templates::find($request->id);
        $temp->title = $request->title;
        $temp->type = $request->type;
        $temp->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Edit Template Info in Template: '{$request->id}' set as Title: {$request->title}, Type: {$request->type}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksRemovePage(){
        $temp = Task_templates::find($_POST['template_id']);
        $temp->pages = $temp->pages - 1;
        $temp->save();

        $temp_page = Task_templates_pages::find($_POST['page_id']);
        $temp_page->delete();

        Task_templates_fields::where('field_page', $_POST['page_id'])->delete();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Remove Page in Template: ".$_POST['template_id'].", Title: {$temp_page->title}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksAddField(){
        $temp_field = new Task_templates_fields();
        $temp_field->template_id = $_POST['template_id'];
        $temp_field->field_page = $_POST['field_page'];
        $temp_field->field_type = $_POST['field_type'];
        $temp_field->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Added Field in Template: ".$_POST['template_id'].", Field Type: ".$_POST['field_type']." , Field Page: ".$_POST['field_page']."";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksSaveFieldInput(Request $request){
        $validator = Validator::make($request->all(), [
            'field_name' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $options = null;

        if ($request->has('options')) {
            $options = $request->input('options', []);
        } else if ($request->has('option')) {
            $options = $request->input('option');
        }


        if (empty($options)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Options are missing or invalid.'
            ]);
        }

        $preAnswers = $request->input('field_pre_answer');
        $isChecked = $request->boolean('is_required') ? 1 : 0;
        $content_id = $request->input('content');

        $formattedData = [];

        if (!$request->has('option')) {
            foreach ($options as $contentId => $optionSet) {
                $formattedData[$contentId] = [
                    'options' => array_values($optionSet),
                ];
            }
        }

        $temp_field = Task_templates_fields::find($content_id);
        $temp_field->field_name = $request->field_name;
        $temp_field->field_label = $request->field_name;
        $temp_field->field_description = $request->field_description;
        $temp_field->is_required = $isChecked;
        $temp_field->field_pre_answer = $preAnswers;
        if ($request->has('options')) {
            $temp_field->options = json_encode($formattedData);
        } else if ($request->has('option')) {
            $temp_field->options = $options;
        }
        $temp_field->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Edit Field as Field Name: {$request->field_name} in Template: {$temp_field->template_id}";
        $log->description = date('Y-m-d');
        $log->save();

        // Now you have structured options as an object-like array
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadTasksGetOptionRadio(Request $request){
        // Fetch the field
        $field = Task_templates_fields::find($request->id);

        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'options' => $options,
            'answer' => $field_pre_answer
        ]);
    }

    public function HeadTasksGetOptionDown(Request $request){
        // Fetch the field
        $field = Task_templates_fields::find($request->id);

        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'options' => $options,
            'answer' => $field_pre_answer
        ]);
    }

    public function HeadTasksRemoveFieldRow(Request $request){
        $field = Task_templates_fields::find($request->id);
        $field->delete();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Delete Field as Field Name: {$field->field_name} in Template: {$field->template_id}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadTasksEditFieldTask(Request $request){
        $id = $request->content;
        $field = Task_templates_fields::find($id);

        return response()->json([
            'field' => $field,
        ]);
    }

    public function HeadTasksChangeStepperTask(Request $request){
        $temp = Task_templates::find($request->id);
        $temp->stepper = $request->stepper;
        $temp->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "On Template Stepper in Template: {$temp->id}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksViewDistributeTaskDepartment(Request $request){
        $temp = Task_templates::find($request->id);
        $id = $temp->department_id;

        // Fetch all departments where department_id is NOT equal to $id
        $dept = Department::where('id', '!=', $id)->get();


        return response()->json(['dept' => $dept]);
    }

    public function HeadTasksCheckDistributeTaskDepartment(Request $request){
        $temp = $request->temp;
        $dept = $request->dept;

        $template = Task_templates::find($temp);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        // Fetch the fields related to the template
        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        $missing_messages = [];
        foreach ($fields as $field) {
            $missing = [];

            if (is_null($field->field_name)) {
                $missing[] = 'Field Name';
            }
            if (is_null($field->options)) {
                $missing[] = 'Options';
            }
            if (is_null($field->field_label)) {
                $missing[] = 'Field Label';
            }

            if (!empty($missing)) {
                $missing_messages[] = "Field ID {$field->id} is missing: " . implode(', ', $missing);
            }
        }

        if (!empty($missing_messages)) {
            return response()->json([
                'status' => 'error',
                'message' => "Some fields have null values:\n" . implode("\n", $missing_messages)
            ]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadTasksSubmitDistributeTaskDepartment(Request $request){
        $temp = $request->temp;
        $dept = $request->dept;

        $template = Task_templates::find($temp);

        $task = new Task_templates();
        $task->title = $template->title;
        $task->department_id = $dept;
        $task->created_by = Auth::user()->name;
        $task->type = $template->type;
        $task->pages = $template->pages;
        $task->save();

        $new_task_id = $task->id;

        $pages = Task_templates_pages::where('template_id', $template->id)->get();

        $page_id_map = [];
        foreach ($pages as $page) {
            $new_pages = new Task_templates_pages();
            $new_pages->template_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_templates_fields();
            $new_field->template_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();
        }

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Distribute Task: {$template->title} From: Department ID {$template->department_id} To: Department ID {$dept}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksViewListSoloAssignTask(Request $request){
        $id = $request->dept;
        $members = Member::where('department_id', $id)->get();

        $users = $members->map(function ($member) use ($request) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find($request->dept);
            $user = User::find($member->user_id);
            $count = $count_solo + $count_group;
            return [
                'profile' => $user,
                'count' => $count,
                'department' => $department->name
            ];
        });

        return response()->json(['users' => $users]);
    }

    public function HeadTasksSubmitAssignSoloTask(Request $request){
        $validator = Validator::make($request->all(), [
            'due' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $due = $request->due;
        $dept = $request->dept;
        $temp = $request->temp;
        $user = $request->user;

        $existSolo = Task_solo::where('user_id', $user)->first();
        if($existSolo){
            $task_id = $existSolo->task_id;
            $existTask = Task::where('id', $task_id)->where('template_id', $temp)->where('status', 'Ongoing');
            if ($existTask && !isset($request->reassign)) {
                return response()->json(['status' => 'existTask']);
            }
        }

        $templateCheck = Task_templates::find($temp);

        if (!$templateCheck) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        // Fetch the fields related to the template
        $fieldsCheck = Task_templates_fields::where('template_id', $templateCheck->id)->get();

        $missing_messages = [];
        foreach ($fieldsCheck as $fieldCheck) {
            $missing = [];

            if (is_null($fieldCheck->field_name)) {
                $missing[] = 'Field Name';
            }
            if (is_null($fieldCheck->options)) {
                $missing[] = 'Options';
            }
            if (is_null($fieldCheck->field_label)) {
                $missing[] = 'Field Label';
            }

            if (!empty($missing)) {
                $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
            }
        }

        if (!empty($missing_messages)) {
            return response()->json([
                'status' => 'errorTask',
                'message' => "Some fields have null values:\n" . implode("\n", $missing_messages)
            ]);
        }

        $template = Task_templates::find($temp);
        $profile = User::find($user);

        $task = new Task();
        $task->title = $template->title;
        $task->department_id = $dept;
        $task->template_id = $temp;
        $task->assigned = date('Y-m-d');
        $task->assigned_to = $profile->name;
        $task->assigned_by = Auth::user()->name;
        $task->due = $due;
        $task->type = $template->type;
        $task->status = 'Ongoing';
        $task->pages = $template->pages;
        $task->progress_percentage = 0.00;
        $task->save();

        $new_task_id = $task->id;

        $solo = new Task_solo();
        $solo->task_id = $new_task_id;
        $solo->user_id = $user;
        $solo->save();

        $pages = Task_templates_pages::where('template_id', $template->id)->get();

        $page_id_map = [];
        foreach ($pages as $page) {
            $new_pages = new Task_pages();
            $new_pages->task_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_fields();
            $new_field->task_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();
        }

        $notif = new Notification();
        $notif->user_id = $user;
        $notif->message = 'New Assigned Solo Task';
        $notif->type = 'info';
        $notif->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Assign Solo Task: {$template->title} User: {$profile->name} ID: {$profile->id}";
        $log->description = date('Y-m-d');
        $log->save();

        if($profile->id != Auth::id()){
            $taskUrl = '';
            if ($profile->role == 'observer') {
                $taskUrl = 'https://tribo.uno/observer/etasks/'.$task->id;
            } elseif ($profile->role == 'employee') {
                $taskUrl = 'https://tribo.uno/employee/etasks/'.$task->id;
            } elseif ($profile->role == 'intern') {
                $taskUrl = 'https://tribo.uno/intern/etasks/'.$task->id;
            }
            $subject = "New Task Assigned: {$task->title}";
            $body = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>New Task Assignment | Tribo Corporation</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                    body {
                        font-family: 'Poppins', Arial, sans-serif;
                        background-color: #f8f9fa;
                        margin: 0;
                        padding: 0;
                        line-height: 1.6;
                        color: #444;
                    }
                    .email-wrapper {
                        max-width: 640px;
                        margin: 20px auto;
                        background: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    }
                    .email-header {
                        background: linear-gradient(135deg, #4C52E4 0%, #3a40c9 100%);
                        padding: 32px 20px;
                        text-align: center;
                        border-bottom: 4px solid #2f35b5;
                    }
                    .email-header h1 {
                        color: #fff;
                        font-size: 24px;
                        font-weight: 600;
                        margin: 0;
                        letter-spacing: 0.5px;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                    }
                    .email-content {
                        padding: 40px;
                    }
                    .greeting {
                        font-size: 18px;
                        margin-bottom: 25px;
                    }
                    .task-card {
                        background: #f5f6ff;
                        border-radius: 10px;
                        padding: 25px;
                        margin: 25px 0;
                        border-left: 4px solid #4C52E4;
                    }
                    .task-row {
                        display: flex;
                        margin-bottom: 12px;
                        align-items: flex-start;
                    }
                    .task-label {
                        font-weight: 600;
                        color: #555;
                        min-width: 120px;
                        flex-shrink: 0;
                    }
                    .task-value {
                        color: #333;
                    }
                    .priority-indicator {
                        display: inline-block;
                        background: #4C52E4;
                        color: white;
                        padding: 4px 10px;
                        border-radius: 4px;
                        font-size: 12px;
                        font-weight: 600;
                        margin-left: 10px;
                        text-transform: uppercase;
                    }
                    .cta-button {
                        display: inline-block;
                        background: linear-gradient(135deg, #4C52E4 0%, #3a40c9 100%);
                        color: #fff !important;
                        text-decoration: none;
                        padding: 14px 32px;
                        border-radius: 6px;
                        font-weight: 500;
                        margin: 25px 0;
                        box-shadow: 0 2px 8px rgba(76, 82, 228, 0.3);
                        transition: all 0.3s ease;
                    }
                    .cta-button:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(76, 82, 228, 0.4);
                    }
                    .footer {
                        background: #4C52E4;
                        padding: 20px;
                        text-align: center;
                        color: rgba(255,255,255,0.9);
                        font-size: 12px;
                        line-height: 1.4;
                    }
                    .urgency-note {
                        background: #fff5f5;
                        border-left: 4px solid #ff4d4d;
                        padding: 15px;
                        margin: 25px 0;
                        border-radius: 4px;
                        font-size: 14px;
                    }
                    .company-info {
                        margin-top: 30px;
                        font-size: 14px;
                        color: #666;
                        border-top: 1px solid #eee;
                        padding-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='email-wrapper'>
                    <div class='email-header'>
                        <h1>NEW TASK ASSIGNMENT</h1>
                    </div>

                    <div class='email-content'>
                        <p class='greeting'>Dear {$profile->name},</p>

                        <p>You've been assigned a new task that requires your attention. Please review the details below:</p>

                        <div class='task-card'>
                            <div class='task-row'>
                                <div class='task-label'>Task Title:</div>
                                <div class='task-value'>{$task->title}</div>
                            </div>
                            <div class='task-row'>
                                <div class='task-label'>Due Date:</div>
                                <div class='task-value'>
                                    {$task->due}
                                    <span class='priority-indicator'>Priority</span>
                                </div>
                            </div>
                            <div class='task-row'>
                                <div class='task-label'>Assigned By:</div>
                                <div class='task-value'>{$task->assigned_by}</div>
                            </div>
                            <div class='task-row'>
                                <div class='task-label'>Status:</div>
                                <div class='task-value'>{$task->status}</div>
                            </div>
                        </div>

                        <div class='urgency-note'>
                            <strong>⚠️ Important:</strong> Please complete this task before the due date to avoid delays in our workflow.
                        </div>

                        <center>
                            <a href='{$taskUrl}' class='cta-button'>VIEW TASK DETAILS</a>
                        </center>

                        <p>If you have any questions about this assignment or need additional resources, please contact your supervisor immediately.</p>

                        <div class='company-info'>
                            <strong>Tribo Corporation</strong><br>
                            <span style='color: #4C52E4;'>✉️</span> tribo.corp@tribo.uno<br>
                            <span style='color: #4C52E4;'>🕒</span> Support Hours: Mon-Fri, 9AM-6PM
                        </div>
                    </div>

                    <div class='footer'>
                        © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                        This is an automated message - please do not reply directly
                    </div>
                </div>
            </body>
            </html>
            ";

            // Use the PHPMailerService to send the email
            $mailer = new PHPMailerService();
            $mailer->sendMail($profile->email, $profile->name, $subject, $body);

            $smsService = new PhilSMSService();
            $message = "New Task from Tribo:\n"
            . "{$task->title}\n"
            . "Due: {$task->due} (Priority)\n"
            . "Status: {$task->status}\n"
            . "Assigned by: {$task->assigned_by}\n"
            . "Details: {$taskUrl}\n"
            . "Please complete before due date.";
            $response = $smsService->sendSMS($profile->phone, $message);
        }

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksCreate(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'type' => ['required','string'],
            'department_id' => ['required','int'],
        ], [
            'title.required' => 'The name field is required.',
            'title.string' => 'The name must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all(),
            ]);
        }

        $task = new Task_templates();
        $task->title = $request->input('title');
        $task->department_id = $request->department_id;
        $task->created_by = Auth::user()->name;
        $task->type = $request->type;
        $task->pages = 0;
        $task->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Create Task Template as Name: {$request->title} Type: {$request->type}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksViewListGroupAssignTask(Request $request){
        if (!isset($request->cont)) {
            $continue = Task_tempo_group::where('template_id', $request->temp)
                ->where('adder_id', Auth::user()->id)
                ->get();

            if ($continue->isNotEmpty() && (!isset($request->assigning) || $request->assigning != 1)) {
                return response()->json(['status' => 'tempoMember']);
            }
        } elseif ($request->cont == 0) {
            // If cont == 0, delete previous selections
            Task_tempo_group::where('template_id', $request->temp)
                ->where('adder_id', Auth::user()->id)
                ->delete();
        }

        $id = $request->dept;
        $members = Member::where('department_id', $id)->get();

        $tempo = Task_tempo_group::where('template_id', $request->temp)->where('adder_id', Auth::user()->id)->get();

        $tempoUserIds = $tempo->pluck('user_id')->toArray();

        $users = $members->map(function ($member) use ($request) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find($request->dept);
            $user = User::find($member->user_id);

            return [
                'profile' => $user,
                'count' => $count_solo + $count_group,
                'department' => $department ? $department->name : 'Unknown'
            ];
        })
        ->reject(function ($user) use ($tempoUserIds) {
            // Reject users whose id is in $tempoUserIds
            return in_array($user['profile']->id, $tempoUserIds);
        })
        ->values();

        // Fetch Selected Users
        $selected = Task_tempo_group::where('template_id', $request->temp)
            ->where('adder_id', Auth::user()->id)
            ->get();

        $selectedUser = $selected->map(function ($select) {
            return ['profile' => User::find($select->user_id)];
        });

        return response()->json(['users' => $users, 'selectedUser' => $selectedUser]);
    }

    public function HeadTasksAddTemporaryAssignGroupTask(Request $request){
        $tempo = new Task_tempo_group();
        $tempo->user_id = $request->user;
        $tempo->template_id = $request->temp;
        $tempo->adder_id = Auth::user()->id;
        $tempo->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksRemoveTemporaryAssignGroupTask(Request $request){
        Task_tempo_group::where('user_id', $request->user)->where('template_id', $request->temp)->where('adder_id', Auth::user()->id)->delete();
        return response()->json(['status' => 'success']);
    }

    public function HeadTasksSubmitAssignGroupTask(Request $request){
        $validator = Validator::make($request->all(), [
            'due' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $due = $request->due;
        $dept = $request->dept;
        $temp = $request->temp;
        $user = $request->user;


        $existGroupArr = [];
        $existMembers = Task_tempo_group::where('template_id', $temp)
        ->where('adder_id', Auth::user()->id)
        ->get();
        if(!$request->has('reassign')){
            foreach($existMembers as $eMember){
                $exist = [];
                $existGroup = Task_group::where('user_id', $eMember->user_id)->first();

                if($existGroup){
                    $existTask = Task::where('id', $existGroup->task_id)
                                ->where('template_id', $temp)
                                ->where('status', 'Ongoing')->first();
                    $exist[] = 'Task Name: '.$existTask->title;
                }

                if(!empty($exist)){
                    $infoExist = User::find($eMember->user_id);
                    $existGroupArr[] = "{$infoExist->name} is currently handling same task: " . implode(', ', $exist);
                }

            }
        }

        if (!empty($existGroupArr) && !$request->has('reassign')) {
            return response()->json([
                'status' => 'existTask',
                'message' => "List of user with same task:\n". implode("\n", $existGroupArr)
            ]);
        }

        $template = Task_templates::find($temp);
        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        // Fetch the fields related to the template
        $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
        $missing_messages = [];

        foreach ($fieldsCheck as $fieldCheck) {
            $missing = [];

            if (is_null($fieldCheck->field_name)) {
                $missing[] = 'Field Name';
            }
            if (is_null($fieldCheck->options)) {
                $missing[] = 'Options';
            }
            if (is_null($fieldCheck->field_label)) {
                $missing[] = 'Field Label';
            }

            if (!empty($missing)) {
                $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
            }
        }

        if (!empty($missing_messages)) {
            return response()->json([
                'status' => 'errorTask',
                'message' => "Some fields have null values:\n" . implode("\n", $missing_messages)
            ]);
        }

        $profileNames = [];
        $groupProfile = Task_tempo_group::where('template_id', $temp)
        ->where('adder_id', Auth::user()->id)
        ->with('user') // Eager load the user relationship (if defined)
        ->get();

        foreach($groupProfile as $group){
            $profile = $group->user; // Access the eager-loaded user relationship
            if ($profile) {
                $profileNames[] = $profile->name;
            }
        }

        $task = new Task();
        $task->title = $template->title;
        $task->department_id = $dept;
        $task->template_id = $temp;
        $task->assigned = date('Y-m-d');
        $task->assigned_to = !empty($profileNames) ? implode(", ", $profileNames) : 'No users assigned';
        $task->assigned_by = Auth::user()->name;
        $task->due = $due;
        $task->type = $template->type;
        $task->status = 'Ongoing';
        $task->pages = $template->pages;
        $task->progress_percentage = 0.00;
        $task->save();

        $new_task_id = $task->id;

        $groupMembers = Task_tempo_group::where('template_id', $temp)
        ->where('adder_id', Auth::user()->id)
        ->get();

        foreach($groupMembers as $groups){
            $group = new Task_group();
            $group->task_id = $new_task_id;
            $group->user_id = $groups->user_id;
            $group->save();
        }

        $pages = Task_templates_pages::where('template_id', $template->id)->get();
        $page_id_map = [];

        foreach ($pages as $page) {
            $new_pages = new Task_pages();
            $new_pages->task_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_fields();
            $new_field->task_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();
        }

        foreach($groupMembers as $gnotif) {

            $notif = new Notification();
            $notif->user_id = $gnotif->user_id;
            $notif->message = 'New Assigned Group Task';
            $notif->type = 'info';
            $notif->save();

            $profile = User::find($gnotif->user_id);
            if($profile->id != Auth::id()){
                $taskUrl = '';
                if ($profile->role == 'observer') {
                    $taskUrl = 'https://tribo.uno/observer/etasks/'.$task->id;
                } elseif ($profile->role == 'employee') {
                    $taskUrl = 'https://tribo.uno/employee/etasks/'.$task->id;
                } elseif ($profile->role == 'intern') {
                    $taskUrl = 'https://tribo.uno/intern/etasks/'.$task->id;
                }
                $subject = "New Task Assigned: {$task->title}";
                $body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>New Task Assignment | Tribo Corporation</title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                        body {
                            font-family: 'Poppins', Arial, sans-serif;
                            background-color: #f8f9fa;
                            margin: 0;
                            padding: 0;
                            line-height: 1.6;
                            color: #444;
                        }
                        .email-wrapper {
                            max-width: 640px;
                            margin: 20px auto;
                            background: #ffffff;
                            border-radius: 12px;
                            overflow: hidden;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                        }
                        .email-header {
                            background: linear-gradient(135deg, #4C52E4 0%, #3a40c9 100%);
                            padding: 32px 20px;
                            text-align: center;
                            border-bottom: 4px solid #2f35b5;
                        }
                        .email-header h1 {
                            color: #fff;
                            font-size: 24px;
                            font-weight: 600;
                            margin: 0;
                            letter-spacing: 0.5px;
                            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                        }
                        .email-content {
                            padding: 40px;
                        }
                        .greeting {
                            font-size: 18px;
                            margin-bottom: 25px;
                        }
                        .task-card {
                            background: #f5f6ff;
                            border-radius: 10px;
                            padding: 25px;
                            margin: 25px 0;
                            border-left: 4px solid #4C52E4;
                        }
                        .task-row {
                            display: flex;
                            margin-bottom: 12px;
                            align-items: flex-start;
                        }
                        .task-label {
                            font-weight: 600;
                            color: #555;
                            min-width: 120px;
                            flex-shrink: 0;
                        }
                        .task-value {
                            color: #333;
                        }
                        .priority-indicator {
                            display: inline-block;
                            background: #4C52E4;
                            color: white;
                            padding: 4px 10px;
                            border-radius: 4px;
                            font-size: 12px;
                            font-weight: 600;
                            margin-left: 10px;
                            text-transform: uppercase;
                        }
                        .cta-button {
                            display: inline-block;
                            background: linear-gradient(135deg, #4C52E4 0%, #3a40c9 100%);
                            color: #fff !important;
                            text-decoration: none;
                            padding: 14px 32px;
                            border-radius: 6px;
                            font-weight: 500;
                            margin: 25px 0;
                            box-shadow: 0 2px 8px rgba(76, 82, 228, 0.3);
                            transition: all 0.3s ease;
                        }
                        .cta-button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 4px 12px rgba(76, 82, 228, 0.4);
                        }
                        .footer {
                            background: #4C52E4;
                            padding: 20px;
                            text-align: center;
                            color: rgba(255,255,255,0.9);
                            font-size: 12px;
                            line-height: 1.4;
                        }
                        .urgency-note {
                            background: #fff5f5;
                            border-left: 4px solid #ff4d4d;
                            padding: 15px;
                            margin: 25px 0;
                            border-radius: 4px;
                            font-size: 14px;
                        }
                        .company-info {
                            margin-top: 30px;
                            font-size: 14px;
                            color: #666;
                            border-top: 1px solid #eee;
                            padding-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-wrapper'>
                        <div class='email-header'>
                            <h1>NEW TASK ASSIGNMENT</h1>
                        </div>

                        <div class='email-content'>
                            <p class='greeting'>Dear {$profile->name},</p>

                            <p>You've been assigned a new task that requires your attention. Please review the details below:</p>

                            <div class='task-card'>
                                <div class='task-row'>
                                    <div class='task-label'>Task Title:</div>
                                    <div class='task-value'>{$task->title}</div>
                                </div>
                                <div class='task-row'>
                                    <div class='task-label'>Due Date:</div>
                                    <div class='task-value'>
                                        {$task->due}
                                        <span class='priority-indicator'>Priority</span>
                                    </div>
                                </div>
                                <div class='task-row'>
                                    <div class='task-label'>Assigned By:</div>
                                    <div class='task-value'>{$task->assigned_by}</div>
                                </div>
                                <div class='task-row'>
                                    <div class='task-label'>Status:</div>
                                    <div class='task-value'>{$task->status}</div>
                                </div>
                            </div>

                            <div class='urgency-note'>
                                <strong>⚠️ Important:</strong> Please complete this task before the due date to avoid delays in our workflow.
                            </div>

                            <center>
                                <a href='{$taskUrl}' class='cta-button'>VIEW TASK DETAILS</a>
                            </center>

                            <p>If you have any questions about this assignment or need additional resources, please contact your supervisor immediately.</p>

                            <div class='company-info'>
                                <strong>Tribo Corporation</strong><br>
                                <span style='color: #4C52E4;'>✉️</span> tribo.corp@tribo.uno<br>
                                <span style='color: #4C52E4;'>🕒</span> Support Hours: Mon-Fri, 9AM-6PM
                            </div>
                        </div>

                        <div class='footer'>
                            © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                            This is an automated message - please do not reply directly
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Use the PHPMailerService to send the email
                $mailer = new PHPMailerService();
                $mailer->sendMail($profile->email, $profile->name, $subject, $body);

                $smsService = new PhilSMSService();
                $message = "New Task from Tribo:\n"
                . "{$task->title}\n"
                . "Due: {$task->due} (Priority)\n"
                . "Status: {$task->status}\n"
                . "Assigned by: {$task->assigned_by}\n"
                . "Details: {$taskUrl}\n"
                . "Please complete before due date.";
                $response = $smsService->sendSMS($profile->phone, $message);
            }

        }

        Task_tempo_group::where('template_id', $temp)
        ->where('adder_id', Auth::user()->id)
        ->delete();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Assign Group Task: {$template->title} User: ".implode(", ", $profileNames);
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksArchiveTemplate(Request $request){
        $template = Task_templates::find($request->temp);

        $taskCheck = Task::where('template_id', $request->temp)->exists();
        if($taskCheck){
            return response()->json(['status' => 'exist']);
        }

        $template->is_archived = 1;
        $template->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Task Template Archived Task Title: {$template->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksRetrievetemp(Request $request){
        $temp = Task_templates::find($request->temp);

        if(!$temp){
            return response()->json([
                'status' => 'error',
                'message' => 'This template might be deleted already please check again'
            ]);
        }

        $temp->is_archived = 0;
        $temp->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Template Retrieve. Template Title: {$temp->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksDeletetemp(Request $request){
        $template = Task_templates::find($request->temp);

        if(!$template){
            return response()->json([
                'status' => 'error',
                'message' => 'This template might be deleted already please check again'
            ]);
        }

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Template Delete. Template Title: {$template->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        Task_templates::where('id', $request->temp)->delete();
        Task_templates_pages::where('template_id', $request->temp)->delete();
        Task_templates_fields::where('template_id', $request->temp)->delete();
        Task_group_auto::where('template_id', $request->temp)->delete();
        Task_group_member_auto::where('template_id', $request->temp)->delete();
        Task_group_tempo_member_auto::where('template_id', $request->temp)->delete();
        Task_solo_auto::where('template_id', $request->temp)->delete();
        Task_tempo_group::where('template_id', $request->temp)->delete();

        return response()->json(['status' => 'success']);
    }

    public function HeadLiveViewTasks($task){
        $info = Task::find($task);
        $task_id = $task;
        if(empty($info)) {
            abort(404);
        }

        $pages = Task_pages::where('task_id', $task)->get();

        $pagesWithContent = $pages->map(function ($page) {
            $contents = Task_fields::where('field_page', $page->id)->get();

            $contents = $contents->map(function ($content) {
                $answer = Task_inputs::where('field_id', $content->id)
                    ->where('task_id', $content->task_id) // Ensure task_id matches
                    ->value('value'); // Fetch the answer value

                $content->answer = $answer; // Attach answer to the field object
                return $content;
            });

            return [
                'pages' => $page,
                'contents' => $contents,
            ];
        });

        $inputValues = collect(); // Initialize an empty collection

        foreach ($pagesWithContent as $row) {
            if (!empty($row['contents'])) {
                foreach ($row['contents'] as $content) {
                    $taskInputs = Task_inputs::where('field_id', $content->id)
                    ->whereNotNull('value')
                    ->get();
                    $inputValues = $inputValues->merge($taskInputs);
                }
            }
        }

        return view('head.head_lvtasks', compact('info', 'task_id', 'inputValues', 'pages', 'pagesWithContent'));
    }

    public function HeadGetLiveViewTasks(Request $request){
        $task_id = $request->task;

        $pages = Task_pages::where('task_id', $task_id)->get();

        $pagesWithContent = $pages->map(function ($page) {
            $content = Task_fields::where('field_page', $page->id)->get();
            foreach($content as $item){
                $item->field_value = Task_inputs::where('field_id', $item->id)->exists()
                ? Task_inputs::where('field_id', $item->id)->value('value')
                : "false";
            }

            return [
                'pages' => $page,
                'contents' => $content,
            ];
        });

        $task = Task::find($task_id);


        return response()->json(['pagesWithContent' => $pagesWithContent, 'page' => $pages, 'task' => $task]);
    }

    public function HeadTasksGetRadio(Request $request){
        // Fetch the field
        $field = Task_fields::find($request->id);
        $field->field_value = Task_inputs::where('field_id', $field->id)->value('value');
        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'input' => $field->field_value,
            'options' => $options,
            'answer' => $field_pre_answer
        ]);
    }

    public function HeadTasksGetDown(Request $request){
        // Fetch the field
        $field = Task_fields::find($request->id);

        $inputs = Task_inputs::where('field_id', $field->id)->first();
        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'options' => $options,
            'answer' => $inputs->value
        ]);
    }

    public function HeadTasksLiveReloading(Request $request){
        $id = $request->id;

        $fields = Task_fields::where('task_id', $id)->get();
        $inputs = Task_inputs::where('task_id', $id)->get()->keyBy('field_id');

        // Build an associative array for current input timestamps: [id => updated_at]
        $currentSnapshot = [];
        foreach ($fields as $field) {
            if (isset($inputs[$field->id])) { // Only consider fields with inputs
                $currentSnapshot[$field->id] = (string) $inputs[$field->id]->updated_at;
            }
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastUpdate' => $currentSnapshot,
                'fields'     => $fields,
                'inputs'     => $inputs
            ]);
        }


        $updatedFields = [];
        foreach ($currentSnapshot as $fieldId => $updatedAt) {
            if (!isset($clientSnapshot[$fieldId]) || $clientSnapshot[$fieldId] !== $updatedAt) {
                $updatedField = $fields->where('id', $fieldId)->first();

                if ($updatedField) {
                    $fieldInput = Task_inputs::where('field_id', $updatedField->id)->first();
                    $updatedField->field_value =  $fieldInput ? $fieldInput->value : "false";

                    $updatedFields[] = [
                        'field' => $updatedField
                    ];
                }
            }
        }

        foreach ($inputs as $fieldId => $input) {
            if (!isset($clientSnapshot[$fieldId])) {  // New input found
                $newField = Task_inputs::where('field_id', $updatedField->id)->first();
                if ($newField) {
                    $newField->field_value = $input->value;

                    $updatedFields[] = [
                        'field' => $newField
                    ];
                }
            }
        }

        if (!empty($updatedFields)) {
            return response()->json([
                'status'     => 'fields_updated',
                'lastUpdate' => $currentSnapshot,
                'contents' => $updatedFields // Return multiple fields instead of just one
            ]);
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastUpdate' => $currentSnapshot
        ]);
    }

    public function HeadTasksViewListTaskCheckingAutomation(Request $request){

        $templates = Task_templates::all();
        $checker = Task_check_auto::all();
        $checkTempIds = $checker->pluck('template_id')->toArray();

        $temp = $templates->reject(function ($template) use ($checkTempIds) {
            // Check if the template ID exists in $checkTempIds
            return in_array($template->id, $checkTempIds);
        })->values();

        // Fetch Selected Users
        $selected = Task_check_auto::all();

        return response()->json(['temp' => $temp, 'selected' => $selected]);
    }

//region change here

    public function HeadApproveTask(Request $request){
        $id = $request->task;
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        $task->status = 'Completed';
        $task->approved_by = Auth::user()->name;
        $task->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Approved task: {$task->title} by User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        if($task->type == 'Solo'){
            $solo = Task_solo::where('task_id', $task->id)->first();
            $tasker = User::find($solo->user_id);
            $dept = Member::where('user_id', $solo->user_id)->first();
            $notif = new Notification();
            $notif->user_id = $tasker->id;
            $notif->message = "Your solo task is approved by: ".Auth::user()->name." task name: {$task->title}";
            $notif->type = 'success';
            $notif->save();

            $data = Task_submit_data::where('task_id', $task->id)->where('user_id', $tasker->id)->first();
            $data->task_id = $task->id;
            $data->department_id = $dept->department_id;
            $data->status = ($task->status == 'Overdue' ? 'Overdue' : 'Completed');
            $data->save();

            $profile = User::find($solo->user_id);
            if($profile->id != Auth::id()){
                $taskUrl = '';
                if ($profile->role == 'observer') {
                    $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
                } elseif ($profile->role == 'employee') {
                    $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
                } elseif ($profile->role == 'intern') {
                    $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
                }
                $subject = "Task Completed: {$task->title}";
                $body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Task Completion Notification</title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                        body {
                            font-family: 'Poppins', Arial, sans-serif;
                            background-color: #f8f9fa;
                            margin: 0;
                            padding: 0;
                            line-height: 1.6;
                        }
                        .email-wrapper {
                            max-width: 640px;
                            margin: 20px auto;
                            background: #ffffff;
                            border-radius: 12px;
                            overflow: hidden;
                            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                        }
                        .email-header {
                            background: #4C52E4;
                            padding: 30px 20px;
                            text-align: center;
                            border-bottom: 4px solid #3a40c9;
                        }
                        .email-header h1 {
                            color: #fff;
                            font-size: 24px;
                            font-weight: 600;
                            margin: 0;
                            letter-spacing: 0.5px;
                        }
                        .email-content {
                            padding: 40px;
                        }
                        .completion-badge {
                            background: #e8f5e9;
                            color: #2e7d32;
                            padding: 8px 15px;
                            border-radius: 20px;
                            font-weight: 600;
                            display: inline-block;
                            margin-bottom: 20px;
                        }
                        .task-details {
                            background: #f5f6ff;
                            border-radius: 8px;
                            padding: 25px;
                            margin: 25px 0;
                        }
                        .detail-row {
                            display: flex;
                            margin-bottom: 12px;
                        }
                        .detail-label {
                            font-weight: 600;
                            color: #555;
                            min-width: 120px;
                        }
                        .detail-value {
                            color: #333;
                        }
                        .footer {
                            background: #4C52E4;
                            padding: 20px;
                            text-align: center;
                            color: rgba(255,255,255,0.9);
                            font-size: 12px;
                        }
                        .button {
                            display: inline-block;
                            background: #4C52E4;
                            color: #fff !important;
                            text-decoration: none;
                            padding: 12px 30px;
                            border-radius: 6px;
                            font-weight: 500;
                            margin: 20px 0;
                        }
                        .signature {
                            margin-top: 30px;
                            border-top: 1px solid #eee;
                            padding-top: 20px;
                            font-style: italic;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-wrapper'>
                        <div class='email-header'>
                            <h1>TASK COMPLETION NOTIFICATION</h1>
                        </div>

                        <div class='email-content'>
                            <div class='completion-badge'>✓ Task Completed</div>

                            <p>Dear {$profile->name},</p>

                            <p>We're pleased to inform you that the following task has been successfully completed:</p>

                            <div class='task-details'>
                                <div class='detail-row'>
                                    <div class='detail-label'>Task Title:</div>
                                    <div class='detail-value'>{$task->title}</div>
                                </div>
                                <div class='detail-row'>
                                    <div class='detail-label'>Approved By:</div>
                                    <div class='detail-value'>".Auth::user()->name."</div>
                                </div>
                                <div class='detail-row'>
                                    <div class='detail-label'>Completion Date:</div>
                                    <div class='detail-value'>".date('F j, Y')."</div>
                                </div>
                                <div class='detail-row'>
                                    <div class='detail-label'>Original Due Date:</div>
                                    <div class='detail-value'>{$task->due}</div>
                                </div>
                            </div>

                            <p>You can review the completed task by clicking the button below:</p>

                            <center>
                                <a href='{$taskUrl}' class='button'>Review Completed Task</a>
                            </center>

                            <div class='signature'>
                                <p>Best regards,<br>
                                <strong>Tribo Corporation</strong></p>
                            </div>
                        </div>

                        <div class='footer'>
                            © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                            This is an automated notification - please do not reply directly
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Use the PHPMailerService to send the email
                $mailer = new PHPMailerService();
                $mailer->sendMail($profile->email, $profile->name, $subject, $body);

                $smsService = new PhilSMSService();
                $message = "✅ Task Successfully Completed\n"
                . "{$task->title}\n"
                . "Approved: ".date('M j')." by ".Auth::user()->name."\n"
                . "Was due: {$task->due}\n"
                . "Details: {$taskUrl}\n"
                . "Thank you for your work!";
                $response = $smsService->sendSMS($profile->phone, $message);
            }
        } else if($task->type === 'Group'){
            $group = Task_group::where('task_id', $task->id)->get();
            foreach($group as $member){
                $tasker = User::find($member->user_id);
                $notif = new Notification();
                $notif->user_id = $tasker->id;
                $notif->message = "Your group task is approved by: ".Auth::user()->name." task name: {$task->title}";
                $notif->type = 'success';
                $notif->save();
                $dept = Member::where('user_id', $member->user_id)->first();

                $data = Task_submit_data::where('task_id', $task->id)->where('user_id', $tasker->id)->first();
                $data->task_id = $task->id;
                $data->department_id = $dept->department_id;
                $data->status = ($task->status == 'Overdue' ? 'Overdue' : 'Completed');
                $data->save();

                $profile = User::find($tasker->user_id);
                if($profile->id != Auth::id()){
                    $taskUrl = '';
                    if ($profile->role == 'observer') {
                        $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'employee') {
                        $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'intern') {
                        $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
                    }
                    $subject = "Task Completed: {$task->title}";
                    $body = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Task Completion Notification</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                            body {
                                font-family: 'Poppins', Arial, sans-serif;
                                background-color: #f8f9fa;
                                margin: 0;
                                padding: 0;
                                line-height: 1.6;
                            }
                            .email-wrapper {
                                max-width: 640px;
                                margin: 20px auto;
                                background: #ffffff;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                            }
                            .email-header {
                                background: #4C52E4;
                                padding: 30px 20px;
                                text-align: center;
                                border-bottom: 4px solid #3a40c9;
                            }
                            .email-header h1 {
                                color: #fff;
                                font-size: 24px;
                                font-weight: 600;
                                margin: 0;
                                letter-spacing: 0.5px;
                            }
                            .email-content {
                                padding: 40px;
                            }
                            .completion-badge {
                                background: #e8f5e9;
                                color: #2e7d32;
                                padding: 8px 15px;
                                border-radius: 20px;
                                font-weight: 600;
                                display: inline-block;
                                margin-bottom: 20px;
                            }
                            .task-details {
                                background: #f5f6ff;
                                border-radius: 8px;
                                padding: 25px;
                                margin: 25px 0;
                            }
                            .detail-row {
                                display: flex;
                                margin-bottom: 12px;
                            }
                            .detail-label {
                                font-weight: 600;
                                color: #555;
                                min-width: 120px;
                            }
                            .detail-value {
                                color: #333;
                            }
                            .footer {
                                background: #4C52E4;
                                padding: 20px;
                                text-align: center;
                                color: rgba(255,255,255,0.9);
                                font-size: 12px;
                            }
                            .button {
                                display: inline-block;
                                background: #4C52E4;
                                color: #fff !important;
                                text-decoration: none;
                                padding: 12px 30px;
                                border-radius: 6px;
                                font-weight: 500;
                                margin: 20px 0;
                            }
                            .signature {
                                margin-top: 30px;
                                border-top: 1px solid #eee;
                                padding-top: 20px;
                                font-style: italic;
                                color: #666;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='email-wrapper'>
                            <div class='email-header'>
                                <h1>TASK COMPLETION NOTIFICATION</h1>
                            </div>

                            <div class='email-content'>
                                <div class='completion-badge'>✓ Task Completed</div>

                                <p>Dear {$profile->name},</p>

                                <p>We're pleased to inform you that the following task has been successfully completed:</p>

                                <div class='task-details'>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Task Title:</div>
                                        <div class='detail-value'>{$task->title}</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Approved By:</div>
                                        <div class='detail-value'>".Auth::user()->name."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Completion Date:</div>
                                        <div class='detail-value'>".date('F j, Y')."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Original Due Date:</div>
                                        <div class='detail-value'>{$task->due}</div>
                                    </div>
                                </div>

                                <p>You can review the completed task by clicking the button below:</p>

                                <center>
                                    <a href='{$taskUrl}' class='button'>Review Completed Task</a>
                                </center>

                                <div class='signature'>
                                    <p>Best regards,<br>
                                    <strong>Tribo Corporation</strong></p>
                                </div>
                            </div>

                            <div class='footer'>
                                © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                                This is an automated notification - please do not reply directly
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    // Use the PHPMailerService to send the email
                    $mailer = new PHPMailerService();
                    $mailer->sendMail($profile->email, $profile->name, $subject, $body);

                    $smsService = new PhilSMSService();
                    $message = "✅ Task Successfully Completed\n"
                    . "{$task->title}\n"
                    . "Approved: ".date('M j')." by ".Auth::user()->name."\n"
                    . "Was due: {$task->due}\n"
                    . "Details: {$taskUrl}\n"
                    . "Thank you for your work!";
                    $response = $smsService->sendSMS($profile->phone, $message);
                }
            }
        }
        return response()->json(['status' => 'success']);
    }

    public function HeadDeclineTask(Request $request){
        $id = $request->task;
        $rate = $request->rating;

        $ratingMap = [
            1 => 'Very Dissatisfied',
            2 => 'Dissatisfied',
            3 => 'Neutral',
            4 => 'Satisfied',
            5 => 'Very Satisfied'
        ];

        $type = $ratingMap[$rate] ?? 'Neutral';

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        Task_submit_data::where('task_id', $task->id)->delete();

        $task->status = 'Ongoing';
        $task->approved_by = '';
        $task->save();


        if($task->type === 'Group'){
            $group = Task_group::where('task_id', $id)->get();
            foreach($group as $member){
                $feedback = new Feedback();
                $feedback->user_id = $member->user_id; // Assuming logged-in user is giving feedback
                $feedback->from_id = Auth::user()->id;
                $feedback->from_name = Auth::user()->name;
                $feedback->feedback = $request->feedback;
                $feedback->rate = $rate; // Store the numeric rating
                $feedback->type = $type; // Store the mapped type
                $feedback->status = 'Pending';
                $feedback->save();

                $profile = User::find($member->user_id);
                if($profile->id != Auth::id()){
                    $taskUrl = '';
                    if ($profile->role == 'observer') {
                        $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'employee') {
                        $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'intern') {
                        $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
                    }

                    $subject = "Task Declined: {$task->title}";
                    $body = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Task Declined Notification</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                            body {
                                font-family: 'Poppins', Arial, sans-serif;
                                background-color: #f8f9fa;
                                margin: 0;
                                padding: 0;
                                line-height: 1.6;
                            }
                            .email-wrapper {
                                max-width: 640px;
                                margin: 20px auto;
                                background: #ffffff;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                            }
                            .email-header {
                                background: #4C52E4;
                                padding: 30px 20px;
                                text-align: center;
                                border-bottom: 4px solid #3a40c9;
                            }
                            .email-header h1 {
                                color: #fff;
                                font-size: 24px;
                                font-weight: 600;
                                margin: 0;
                                letter-spacing: 0.5px;
                            }
                            .email-content {
                                padding: 40px;
                            }
                            .status-badge {
                                background: #ffebee;
                                color: #c62828;
                                padding: 8px 15px;
                                border-radius: 20px;
                                font-weight: 600;
                                display: inline-block;
                                margin-bottom: 20px;
                            }
                            .task-details {
                                background: #f5f6ff;
                                border-radius: 8px;
                                padding: 25px;
                                margin: 25px 0;
                                border-left: 4px solid #ff5252;
                            }
                            .detail-row {
                                display: flex;
                                margin-bottom: 12px;
                            }
                            .detail-label {
                                font-weight: 600;
                                color: #555;
                                min-width: 120px;
                            }
                            .detail-value {
                                color: #333;
                            }
                            .reason-box {
                                background: #fff5f5;
                                border-radius: 8px;
                                padding: 20px;
                                margin: 20px 0;
                                border: 1px dashed #ff5252;
                            }
                            .reason-title {
                                font-weight: 600;
                                color: #c62828;
                                margin-bottom: 10px;
                            }
                            .footer {
                                background: #4C52E4;
                                padding: 20px;
                                text-align: center;
                                color: rgba(255,255,255,0.9);
                                font-size: 12px;
                            }
                            .button {
                                display: inline-block;
                                background: #4C52E4;
                                color: #fff !important;
                                text-decoration: none;
                                padding: 12px 30px;
                                border-radius: 6px;
                                font-weight: 500;
                                margin: 20px 0;
                                transition: all 0.3s ease;
                            }
                            .button:hover {
                                background: #3a40c9;
                            }
                            .signature {
                                margin-top: 30px;
                                border-top: 1px solid #eee;
                                padding-top: 20px;
                                color: #666;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='email-wrapper'>
                            <div class='email-header'>
                                <h1>TASK DECLINED NOTIFICATION</h1>
                            </div>

                            <div class='email-content'>
                                <div class='status-badge'>✗ Task Declined</div>

                                <p>Dear {$profile->name},</p>

                                <p>The following task has been declined by the assignee. Please review the details below:</p>

                                <div class='task-details'>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Task Title:</div>
                                        <div class='detail-value'>{$task->title}</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Declined By:</div>
                                        <div class='detail-value'>".Auth::user()->name."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Declined Date:</div>
                                        <div class='detail-value'>".date('F j, Y')."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Original Due Date:</div>
                                        <div class='detail-value'>{$task->due}</div>
                                    </div>
                                </div>

                                <div class='reason-box'>
                                    <div class='reason-title'>REASON FOR DECLINING:</div>
                                    <p>{$request->feedback}</p>
                                </div>

                                <p>You can review the declined task and take appropriate action by clicking the button below:</p>

                                <center>
                                    <a href='{$taskUrl}' class='button'>REVIEW DECLINED TASK</a>
                                </center>

                                <div class='signature'>
                                    <p>Best regards,<br>
                                    <strong>Tribo Corporation</strong></p>
                                </div>
                            </div>

                            <div class='footer'>
                                © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                                This is an automated notification - please do not reply directly
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    // Use the PHPMailerService to send the email
                    $mailer = new PHPMailerService();
                    $mailer->sendMail($profile->email, $profile->name, $subject, $body);

                    $smsService = new PhilSMSService();
                    $message = "⚠️ Task Declined: {$task->title}\n"
                    . "Declined by: ".Auth::user()->name."\n"
                    . "Date: ".date('M j, Y')."\n"
                    . "Due: {$task->due}\n"
                    . "Reason: ".substr($request->feedback, 0, 30).(strlen($request->feedback) > 30 ? '...' : '')."\n"
                    . "Review: {$taskUrl}\n"
                    . "-Tribo Corp";
                    $response = $smsService->sendSMS($profile->phone, $message);
                }
            }
        } else if($task->type === 'Solo'){
            $solo = Task_solo::where('task_id', $id)->first();
            if ($solo) {
                $feedback = new Feedback();
                $feedback->user_id = $solo->user_id;
                $feedback->from_id = Auth::user()->id;
                $feedback->from_name = Auth::user()->name;
                $feedback->feedback = $request->feedback;
                $feedback->rate = $rate;
                $feedback->type = $type;
                $feedback->status = 'Pending';
                $feedback->save();

                $profile = User::find($solo->user_id);
                if($profile->id != Auth::id()){
                    $taskUrl = '';
                    if ($profile->role == 'observer') {
                        $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'employee') {
                        $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
                    } elseif ($profile->role == 'intern') {
                        $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
                    }

                    $subject = "Task Declined: {$task->title}";
                    $body = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Task Declined Notification</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                            body {
                                font-family: 'Poppins', Arial, sans-serif;
                                background-color: #f8f9fa;
                                margin: 0;
                                padding: 0;
                                line-height: 1.6;
                            }
                            .email-wrapper {
                                max-width: 640px;
                                margin: 20px auto;
                                background: #ffffff;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                            }
                            .email-header {
                                background: #4C52E4;
                                padding: 30px 20px;
                                text-align: center;
                                border-bottom: 4px solid #3a40c9;
                            }
                            .email-header h1 {
                                color: #fff;
                                font-size: 24px;
                                font-weight: 600;
                                margin: 0;
                                letter-spacing: 0.5px;
                            }
                            .email-content {
                                padding: 40px;
                            }
                            .status-badge {
                                background: #ffebee;
                                color: #c62828;
                                padding: 8px 15px;
                                border-radius: 20px;
                                font-weight: 600;
                                display: inline-block;
                                margin-bottom: 20px;
                            }
                            .task-details {
                                background: #f5f6ff;
                                border-radius: 8px;
                                padding: 25px;
                                margin: 25px 0;
                                border-left: 4px solid #ff5252;
                            }
                            .detail-row {
                                display: flex;
                                margin-bottom: 12px;
                            }
                            .detail-label {
                                font-weight: 600;
                                color: #555;
                                min-width: 120px;
                            }
                            .detail-value {
                                color: #333;
                            }
                            .reason-box {
                                background: #fff5f5;
                                border-radius: 8px;
                                padding: 20px;
                                margin: 20px 0;
                                border: 1px dashed #ff5252;
                            }
                            .reason-title {
                                font-weight: 600;
                                color: #c62828;
                                margin-bottom: 10px;
                            }
                            .footer {
                                background: #4C52E4;
                                padding: 20px;
                                text-align: center;
                                color: rgba(255,255,255,0.9);
                                font-size: 12px;
                            }
                            .button {
                                display: inline-block;
                                background: #4C52E4;
                                color: #fff !important;
                                text-decoration: none;
                                padding: 12px 30px;
                                border-radius: 6px;
                                font-weight: 500;
                                margin: 20px 0;
                                transition: all 0.3s ease;
                            }
                            .button:hover {
                                background: #3a40c9;
                            }
                            .signature {
                                margin-top: 30px;
                                border-top: 1px solid #eee;
                                padding-top: 20px;
                                color: #666;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='email-wrapper'>
                            <div class='email-header'>
                                <h1>TASK DECLINED NOTIFICATION</h1>
                            </div>

                            <div class='email-content'>
                                <div class='status-badge'>✗ Task Declined</div>

                                <p>Dear {$profile->name},</p>

                                <p>The following task has been declined by the assignee. Please review the details below:</p>

                                <div class='task-details'>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Task Title:</div>
                                        <div class='detail-value'>{$task->title}</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Declined By:</div>
                                        <div class='detail-value'>".Auth::user()->name."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Declined Date:</div>
                                        <div class='detail-value'>".date('F j, Y')."</div>
                                    </div>
                                    <div class='detail-row'>
                                        <div class='detail-label'>Original Due Date:</div>
                                        <div class='detail-value'>{$task->due}</div>
                                    </div>
                                </div>

                                <div class='reason-box'>
                                    <div class='reason-title'>REASON FOR DECLINING:</div>
                                    <p>{$request->feedback}</p>
                                </div>

                                <p>You can review the declined task and take appropriate action by clicking the button below:</p>

                                <center>
                                    <a href='{$taskUrl}' class='button'>REVIEW DECLINED TASK</a>
                                </center>

                                <div class='signature'>
                                    <p>Best regards,<br>
                                    <strong>Tribo Corporation</strong></p>
                                </div>
                            </div>

                            <div class='footer'>
                                © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                                This is an automated notification - please do not reply directly
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    // Use the PHPMailerService to send the email
                    $mailer = new PHPMailerService();
                    $mailer->sendMail($profile->email, $profile->name, $subject, $body);

                    $smsService = new PhilSMSService();
                    $message = "⚠️ Task Declined: {$task->title}\n"
                    . "Declined by: ".Auth::user()->name."\n"
                    . "Date: ".date('M j, Y')."\n"
                    . "Due: {$task->due}\n"
                    . "Reason: ".substr($request->feedback, 0, 30).(strlen($request->feedback) > 30 ? '...' : '')."\n"
                    . "Review: {$taskUrl}\n"
                    . "-Tribo Corp";
                    $response = $smsService->sendSMS($profile->phone, $message);
                }
            }
        }

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Declined task: {$task->title} by User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

//endregion
    public function HeadPrintTasks($task){
        $info = Task::find($task);
        $task_id = $task;
        if(empty($info)) {
            abort(404);
        }

        $pages = Task_pages::where('task_id', $task)->get();

        $pagesWithContent = $pages->map(function ($page) {
            $contents = Task_fields::where('field_page', $page->id)->get();

            $contents = $contents->map(function ($content) {
                $answer = Task_inputs::where('field_id', $content->id)
                    ->where('task_id', $content->task_id) // Ensure task_id matches
                    ->value('value'); // Fetch the answer value

                $content->answer = $answer; // Attach answer to the field object
                return $content;
            });

            return [
                'pages' => $page,
                'contents' => $contents,
            ];
        });

        $inputValues = collect(); // Initialize an empty collection

        foreach ($pagesWithContent as $row) {
            if (!empty($row['contents'])) {
                foreach ($row['contents'] as $content) {
                    $taskInputs = Task_inputs::where('field_id', $content->id)
                    ->whereNotNull('value')
                    ->get();
                    $inputValues = $inputValues->merge($taskInputs);
                }
            }
        }

        return view('head.head_ptasks', compact('info', 'task_id', 'inputValues', 'pages', 'pagesWithContent'));
    }

    public function HeadGetPrintTasks(Request $request){
        $task_id = $request->task;

        $pages = Task_pages::where('task_id', $task_id)->get();

        $pagesWithContent = $pages->map(function ($page) {
            $content = Task_fields::where('field_page', $page->id)->get();
            foreach($content as $item){
                $item->field_value = Task_inputs::where('field_id', $item->id)->exists()
                ? Task_inputs::where('field_id', $item->id)->value('value')
                : "false";
            }

            return [
                'pages' => $page,
                'contents' => $content,
            ];
        });

        $task = Task::find($task_id);


        return response()->json(['pagesWithContent' => $pagesWithContent, 'page' => $pages, 'task' => $task]);
    }

    public function HeadTasksPrintGetRadio(Request $request){
        // Fetch the field
        $field = Task_fields::find($request->id);
        $field->field_value = Task_inputs::where('field_id', $field->id)->value('value');
        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'input' => $field->field_value,
            'options' => $options,
            'answer' => $field_pre_answer
        ]);
    }

    public function HeadTasksPrintGetDown(Request $request){
        // Fetch the field
        $field = Task_fields::find($request->id);

        $inputs = Task_inputs::where('field_id', $field->id)->first();
        // Decode the options
        $options = json_decode($field->options, true);
        $field_pre_answer = json_decode($field->field_pre_answer, true);
        // Display options
        return response()->json([
            'options' => $options,
            'answer' => $inputs->value
        ]);
    }

    public function HeadTasksLivePrintReloading(Request $request){
        $id = $request->id;

        $fields = Task_fields::where('task_id', $id)->get();
        $inputs = Task_inputs::where('task_id', $id)->get()->keyBy('field_id');

        // Build an associative array for current input timestamps: [id => updated_at]
        $currentSnapshot = [];
        foreach ($fields as $field) {
            if (isset($inputs[$field->id])) { // Only consider fields with inputs
                $currentSnapshot[$field->id] = (string) $inputs[$field->id]->updated_at;
            }
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastUpdate' => $currentSnapshot,
                'fields'     => $fields,
                'inputs'     => $inputs
            ]);
        }


        $updatedFields = [];
        foreach ($currentSnapshot as $fieldId => $updatedAt) {
            if (!isset($clientSnapshot[$fieldId]) || $clientSnapshot[$fieldId] !== $updatedAt) {
                $updatedField = $fields->where('id', $fieldId)->first();

                if ($updatedField) {
                    $fieldInput = Task_inputs::where('field_id', $updatedField->id)->first();
                    $updatedField->field_value =  $fieldInput ? $fieldInput->value : "false";

                    $updatedFields[] = [
                        'field' => $updatedField
                    ];
                }
            }
        }

        foreach ($inputs as $fieldId => $input) {
            if (!isset($clientSnapshot[$fieldId])) {  // New input found
                $newField = Task_inputs::where('field_id', $updatedField->id)->first();
                if ($newField) {
                    $newField->field_value = $input->value;

                    $updatedFields[] = [
                        'field' => $newField
                    ];
                }
            }
        }

        if (!empty($updatedFields)) {
            return response()->json([
                'status'     => 'fields_updated',
                'lastUpdate' => $currentSnapshot,
                'contents' => $updatedFields // Return multiple fields instead of just one
            ]);
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastUpdate' => $currentSnapshot
        ]);
    }

    public function HeadDistributeTask(Request $request){
        $tasks = $request->task;
        $to = $request->department_id;
        $cont = $request->cont;

        $task = Task::where('id', $tasks)
        ->where('status', 'Completed')
        ->first();

        if($task){

            $dist = Department::where('id', $to)->first();
            if($dist->id === $task->department_id){
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot distribute this task to same department'
                ]);
            }
            if ($dist) {
                $data = [
                    'template_id' => $task->template_id,
                    'department_id' => $task->department_id,
                    'to_department_id' => $to,
                    'task_id' => $task->id,
                    'adder_id' => Auth::user()->id,
                    'title' => $task->title,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $checkFirst = Task_distribute_auto::where('task_id', $tasks)->latest()->first();
                if($cont == 0 &&  $checkFirst !== null){
                    return response()->json(['status' => 'exist', 'message' => 'Task Status: '.$checkFirst->status]);
                }

                Task_distribute_auto::insert($data);

                $log = new Log();
                $log->name = Auth::user()->name;
                $log->action = "Distribute task to : {$dist->name} id: {$dist->id} task title: {$task->title}";
                $log->description = date('Y-m-d');
                $log->save();
                return response()->json(['status' => 'success']);
            }
        }
    }

    public function HeadAcceptDistributeTask(Request $request){
        $dist = $request->dist;

        $distribute = Task_distribute_auto::find($dist);
        $distribute->status = 'Accepted';
        $distribute->save();

        $getTask = Task::where('id', $distribute->task_id)->first();

        $task = new Task();
        $task->title = $getTask->title;
        $task->department_id = $distribute->to_department_id;
        $task->template_id = $distribute->template_id;
        $task->assigned = $getTask->assigned;
        $task->due = $getTask->due;
        $task->assigned_to = $getTask->assigned_to;
        $task->assigned_by = $getTask->assigned_by;
        $task->approved_by = $getTask->approved_by;
        $task->type = $getTask->type;
        $task->pages = $getTask->pages;
        $task->status = 'Distributed';
        $task->progress_percentage = $getTask->progress_percentage;
        $task->created_at = $getTask->created_at;
        $task->updated_at = $getTask->updated_at;
        $task->save();

        $new_task_id = $task->id;

        $pages = Task_pages::where('task_id', $distribute->task_id)->get();

        $page_id_map = [];
        foreach ($pages as $page) {
            $new_pages = new Task_pages();
            $new_pages->task_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_fields::where('task_id', $distribute->task_id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_fields();
            $new_field->task_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();

            $inputs = Task_inputs::where('field_id', $field->id)->first();
            if($inputs !== null){
                $new_inputs = new Task_inputs();
                $new_inputs->task_id = $new_task_id;
                $new_inputs->field_id = $new_field->id;
                $new_inputs->value = $inputs->value;
                $new_inputs->save();
            }
        }

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Distribute Accept Task: {$task->title} From: Department ID {$task->department_id} To: Department ID {$distribute->to_department_id}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadDeclineDistributeTask(Request $request){
        $dist = $request->dist;

        $distribute = Task_distribute_auto::find($dist);
        $distribute->status = 'Declined';
        $distribute->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Distribute Decline Task: {$distribute->title} From: Department ID {$distribute->department_id} To: Department ID {$distribute->to_department_id}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }
//region To fix in Accept All Distribute
    public function HeadAcceptAllDistributeTask(Request $request){

        $distributes = Task_distribute_auto::where('status', 'Pending')->get();
        foreach($distributes as $distribute){
            $distribute->status = 'Accepted';
            $distribute->save();

            $getTask = Task::where('id', $distribute->task_id)->first();

            $task = new Task();
            $task->title = $getTask->title;
            $task->department_id = $distribute->to_department_id;
            $task->template_id = $distribute->template_id;
            $task->assigned = $getTask->assigned;
            $task->due = $getTask->due;
            $task->assigned_to = $getTask->assigned_to;
            $task->assigned_by = $getTask->assigned_by;
            $task->approved_by = $getTask->approved_by;
            $task->type = $getTask->type;
            $task->pages = $getTask->pages;
            $task->status = 'Distributed';
            $task->progress_percentage = $getTask->progress_percentage;
            $task->created_at = $getTask->created_at;
            $task->updated_at = $getTask->updated_at;
            $task->save();

            $new_task_id = $task->id;

            $pages = Task_pages::where('task_id', $distribute->task_id)->get();

            $page_id_map = [];
            foreach ($pages as $page) {
                $new_pages = new Task_pages();
                $new_pages->task_id = $new_task_id;
                $new_pages->page_title = $page->page_title;
                $new_pages->page_content = $page->page_content;
                $new_pages->save();

                $page_id_map[$page->id] = $new_pages->id;
            }

            $fields = Task_fields::where('task_id', $distribute->task_id)->get();

            foreach ($fields as $field) {
                $new_field = new Task_fields();
                $new_field->task_id = $new_task_id;
                $new_field->field_name = $field->field_name;
                $new_field->field_type = $field->field_type;
                $new_field->is_required = $field->is_required;
                $new_field->options = $field->options;
                $new_field->field_page = $page_id_map[$field->field_page] ?? null;
                $new_field->field_label = $field->field_label;
                $new_field->field_description = $field->field_description;
                $new_field->field_pre_answer = $field->field_pre_answer;
                $new_field->save();

                $inputs = Task_inputs::where('field_id', $field->id)->first();
                if($inputs !== null){
                    $new_inputs = new Task_inputs();
                    $new_inputs->task_id = $new_task_id;
                    $new_inputs->field_id = $new_field->id;
                    $new_inputs->value = $inputs->value;
                    $new_inputs->save();
                }
            }



            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "Distribute Accept Task: {$task->title} From: Department ID {$task->department_id} To: Department ID {$distribute->to_department_id}";
            $log->description = date('Y-m-d');
            $log->save();
        }

        return response()->json(['status' => 'success']);
    }

    public function HeadDeclineAllDistributeTask(Request $request){

        $distributes = Task_distribute_auto::where('status', 'Pending')->get();
        foreach($distributes as $distribute){
            $distribute->status = 'Declined';
            $distribute->save();

            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "Distribute Decline Task: {$distribute->title} From: Department ID {$distribute->department_id} To: Department ID {$distribute->to_department_id}";
            $log->description = date('Y-m-d');
            $log->save();
        }


        return response()->json(['status' => 'success']);
    }
//endregion

    public function HeadTasksViewNewTaskToLink(Request $request){
        $link = Task::find($request->task);

        $templates = Task_templates::all();

        $getMembers = Member::all();

        $soloUsers = $getMembers->map(function ($member) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find($member->department_id);
            $user = User::find($member->user_id);
            $count = $count_solo + $count_group;
            return [
                'profile' => $user,
                'count' => $count,
                'department' => $department->name
            ];
        });

        $tempo = Link_tempo_group::where('task_id', $request->task)->where('adder_id', Auth::user()->id)->get();
        $tempoUserIds = $tempo->pluck('user_id')->toArray();

        $groupUsers = $getMembers->map(function ($member) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find( $member->department_id);
            $user = User::find($member->user_id);

            return [
                'profile' => $user,
                'count' => $count_solo + $count_group,
                'department' => $department ? $department->name : 'Unknown'
            ];
        })
        ->reject(function ($user) use ($tempoUserIds) {
            // Reject users whose id is in $tempoUserIds
            return in_array($user['profile']->id, $tempoUserIds);
        })
        ->values();

        // Fetch Selected Users
        $selected = Link_tempo_group::where('task_id', $request->task)
            ->where('adder_id', Auth::user()->id)
            ->get();

        $selectedGroupUser = $selected->map(function ($select) {
            return ['profile' => User::find($select->user_id)];
        });

        return response()->json(['templates' => $templates, 'soloUsers' => $soloUsers, 'selectedGroupUser' => $selectedGroupUser, 'groupUsers' => $groupUsers, 'name' => $link->title]);
    }

    public function HeadTasksCheckLinkTemp(Request $request){
        $task = $request->task;
        $link = Link_tempo_group::where('task_id', $task)->where('adder_id', Auth::user()->id)->exists();
        if ($link) {
            return response()->json(['status' => 'exist']);
        } else {
            return response()->json(['status' => 'not_exist']);
        }
    }

    public function HeadTasksRemoveLinkTemp(Request $request){
        $task = $request->task;
        Link_tempo_group::where('task_id', $task)->where('adder_id', Auth::user()->id)->delete();
        return response()->json(['status' => 'success']);
    }

    public function HeadTasksAddMemberLinkTemp(Request $request){
        $task = $request->task;
        $user = $request->user;

        $submit = new Link_tempo_group();
        $submit->user_id = $user;
        $submit->task_id = $task;
        $submit->adder_id = Auth::user()->id;
        $submit->save();

        $tempo = Link_tempo_group::where('task_id', $task)->where('adder_id', Auth::user()->id)->get();
        $tempoUserIds = $tempo->pluck('user_id')->toArray();
        $getMembers = Member::all();

        $groupUsers = $getMembers->map(function ($member) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find( $member->department_id);
            $user = User::find($member->user_id);

            return [
                'profile' => $user,
                'count' => $count_solo + $count_group,
                'department' => $department ? $department->name : 'Unknown'
            ];
        })
        ->reject(function ($user) use ($tempoUserIds) {
            // Reject users whose id is in $tempoUserIds
            return in_array($user['profile']->id, $tempoUserIds);
        })
        ->values();

        // Fetch Selected Users
        $selected = Link_tempo_group::where('task_id', $request->task)
            ->where('adder_id', Auth::user()->id)
            ->get();

        $selectedGroupUser = $selected->map(function ($select) {
            return ['profile' => User::find($select->user_id)];
        });

        $datas = [
            'groupUsers' => $groupUsers,
            'selectedGroupUser' =>$selectedGroupUser
        ];

        return response()->json(['status' => 'success', 'datas' => $datas]);
    }

    public function HeadTasksRemoveMemberLinkTemp(Request $request){
        $task = $request->task;
        $user = $request->user;

        Link_tempo_group::where('task_id', $task)->where('user_id', $user)->where('adder_id', Auth::user()->id)->delete();

        $tempo = Link_tempo_group::where('task_id', $task)->where('adder_id', Auth::user()->id)->get();
        $tempoUserIds = $tempo->pluck('user_id')->toArray();
        $getMembers = Member::all();

        $groupUsers = $getMembers->map(function ($member) {
            $count_solo = Task_solo::where('user_id', $member->user_id)->count();
            $count_group = Task_group::where('user_id', $member->user_id)->count();
            $department = Department::find( $member->department_id);
            $user = User::find($member->user_id);

            return [
                'profile' => $user,
                'count' => $count_solo + $count_group,
                'department' => $department ? $department->name : 'Unknown'
            ];
        })
        ->reject(function ($user) use ($tempoUserIds) {
            // Reject users whose id is in $tempoUserIds
            return in_array($user['profile']->id, $tempoUserIds);
        })
        ->values();

        // Fetch Selected Users
        $selected = Link_tempo_group::where('task_id', $request->task)
            ->where('adder_id', Auth::user()->id)
            ->get();

        $selectedGroupUser = $selected->map(function ($select) {
            return ['profile' => User::find($select->user_id)];
        });

        $datas = [
            'groupUsers' => $groupUsers,
            'selectedGroupUser' =>$selectedGroupUser
        ];

        return response()->json(['status' => 'success', 'datas' => $datas]);
    }

    public function HeadTasksLinkAndAssignTask(Request $request){
        $tasks = $request->task;
        $user = $request->user;
        $temp = $request->temp;
        $type = $request->type;

        $validator = Validator::make($request->all(), [
            'due' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Due date must be filled']);
        }

        $task = Task::find($tasks);
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ]);
        }

        // Check task status
        if ($task->status === 'Linked') {
            return response()->json([
                'status' => 'error',
                'message' => "This task has already been linked to another task"
            ]);
        }

        // Handle Solo type
        if ($type == 'Solo') {
            Link_tempo_group::where('task_id', $tasks)
                ->where('adder_id', Auth::user()->id)
                ->delete();
        }

        $due = $request->due;
        $member = Member::where('user_id', $user)->first();
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a department member'
            ]);
        }

        $dept = $member->department_id;

        $existSoloTasks = Task_solo::where('user_id', $user)->get();

        if ($existSoloTasks->isNotEmpty()) { // Ensure there are tasks
            $taskIds = $existSoloTasks->pluck('task_id')->toArray(); // Extract all task IDs

            $existTasks = Task::whereIn('id', $taskIds) // Find tasks with matching IDs
                ->whereIn('status', ['Ongoing', 'Overdue'])
                ->whereNotNull('link_id')
                ->where('type', 'Solo')
                ->get(); // Get all matching tasks
            if ($existTasks->isNotEmpty()) { // If ongoing tasks exist
                if (!isset($request->reassign)) { // If reassign is NOT set
                    return response()->json(['status' => 'existTask', 'tasks' => $existTasks]);
                }
            }
        }

        $template = Task_templates::find($temp);
        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found'
            ]);
        }

        // Department validation
        if ($task->department_id != $template->department_id) {
            $departmentName = Department::find($task->department_id)->name ?? 'another department';
            return response()->json([
                'status' => 'error',
                'message' => "This link task is for {$departmentName}, please try again"
            ]);
        }

        if ($template->department_id != $dept) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot assign this task to a user outside the template department'
            ]);
        }
        // Fetch the fields related to the template
        $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();

        $missing_messages = [];
        foreach ($fieldsCheck as $fieldCheck) {
            $missing = [];

            if (is_null($fieldCheck->field_name)) {
                $missing[] = 'Field Name';
            }
            if (is_null($fieldCheck->options)) {
                $missing[] = 'Options';
            }
            if (is_null($fieldCheck->field_label)) {
                $missing[] = 'Field Label';
            }

            if (!empty($missing)) {
                $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
            }
        }

        if (!empty($missing_messages)) {
            return response()->json([
                'status' => 'errorTask',
                'message' => "Some fields have null values:\n" . implode("\n", $missing_messages)
            ]);
        }

        $template = Task_templates::find($temp);
        $profile = User::find($user);

        $task = new Task();
        $task->title = $template->title;
        $task->department_id = $dept;
        $task->template_id = $temp;
        $task->link_id = $tasks;
        $task->assigned = date('Y-m-d');
        $task->assigned_to = $profile->name;
        $task->assigned_by = Auth::user()->name;
        $task->due = $due;
        $task->type = $template->type;
        $task->status = 'Ongoing';
        $task->pages = $template->pages;
        $task->progress_percentage = 0.00;
        $task->save();

        $new_task_id = $task->id;

        $solo = new Task_solo();
        $solo->task_id = $new_task_id;
        $solo->user_id = $user;
        $solo->save();

        $pages = Task_templates_pages::where('template_id', $template->id)->get();

        $page_id_map = [];
        foreach ($pages as $page) {
            $new_pages = new Task_pages();
            $new_pages->task_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_fields();
            $new_field->task_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();
        }

        $taskLinked = Task::find($tasks);
        $taskLinked->status = 'Linked';
        $taskLinked->save();

        $notif = new Notification();
        $notif->user_id = $user;
        $notif->message = 'New Assigned Solo Task With Linked Task: '.$taskLinked->title.' ID: '.$tasks;
        $notif->type = 'info';
        $notif->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Assign Solo Task: {$template->title} User: {$profile->name} ID: {$profile->id} With Linked Task: {$taskLinked->title} ID: {$tasks}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksGroupLinkAndAssignTask(Request $request){
        $tasks = $request->task;
        $temp = $request->temp;
        $due = $request->due;

        $checkFirst = Task::where('id', $tasks)->first();

        if($checkFirst->status === 'Linked'){
            return response()->json([
                'status' => 'errorTask',
                'message' => "This task does not exist anymore, it might be already linked to a task"
            ]);
        }

        $isMembers = Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->get();

        if ($isMembers->isEmpty() || $isMembers->count() <= 1) {
            return response()->json([
                'status' => 'errorTask',
                'message' => "No users selected or not enough user for this task."
            ]);
        }

        $validator = Validator::make($request->all(), [
            'due' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Due date must be filled']);
        }

        $existGroupArr = [];
        $existMembers = Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->get();
        if(!$request->has('reassign')){
            foreach($existMembers as $eMember){
                $exist = [];
                $existGroup = Task_group::where('user_id', $eMember->user_id)->get();

                if($existGroup->isNotEmpty()){
                    $existTasks = Task::whereIn('status', ['Ongoing', 'Overdue'])
                                ->whereNotNull('link_id')
                                ->where('type', 'Group')
                                ->get();

                    foreach ($existTasks as $existTask) {
                        $exist[] = 'Task Name: ' . $existTask->title;
                    }
                }

                if(!empty($exist)){
                    $infoExist = User::find($eMember->user_id);
                    if ($infoExist) {
                        $existGroupArr[] = "{$infoExist->name} is currently handling the same task: " . implode(', ', $exist);
                    }
                }
            }
        }

        if (!empty($existGroupArr) && !$request->has('reassign')) {
            return response()->json([
                'status' => 'existTask',
                'message' => "List of user with same task:\n". implode("\n", $existGroupArr)
            ]);
        }

        $template = Task_templates::find($temp);
        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        $GetOneMember = Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->get();
        foreach($GetOneMember as $get){
            $getOne = Member::where('user_id', $get->user_id)->first();
            $dept = Department::find($getOne->department_id);

            if ($checkFirst->department_id != $template->department_id) {
                $departmentName = Department::find($checkFirst->department_id)->name ?? 'another department';
                return response()->json([
                    'status' => 'error',
                    'message' => "This link task is for {$departmentName}, please try again"
                ]);
            }

            if ($template->department_id != $dept->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot assign this task to a users outside the template department'
                ]);
            }
        }


        // Fetch the fields related to the template
        $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
        $missing_messages = [];

        foreach ($fieldsCheck as $fieldCheck) {
            $missing = [];

            if (is_null($fieldCheck->field_name)) {
                $missing[] = 'Field Name';
            }
            if (is_null($fieldCheck->options)) {
                $missing[] = 'Options';
            }
            if (is_null($fieldCheck->field_label)) {
                $missing[] = 'Field Label';
            }

            if (!empty($missing)) {
                $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
            }
        }

        if (!empty($missing_messages)) {
            return response()->json([
                'status' => 'errorTask',
                'message' => "Some fields have null values:\n" . implode("\n", $missing_messages)
            ]);
        }

        $profileNames = [];
        $groupProfile = Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->with('user') // Eager load the user relationship (if defined)
        ->get();

        $groupDept =  Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->first();

        $department_id = Task_templates::find($temp);

        foreach($groupProfile as $group){
            $profile = $group->user; // Access the eager-loaded user relationship
            if ($profile) {
                $profileNames[] = $profile->name;
            }
        }

        $task = new Task();
        $task->title = $template->title;
        $task->department_id = $department_id->department_id;
        $task->template_id = $temp;
        $task->assigned = date('Y-m-d');
        $task->assigned_to = !empty($profileNames) ? implode(", ", $profileNames) : 'No users assigned';
        $task->assigned_by = Auth::user()->name;
        $task->due = $due;
        $task->link_id = $tasks;
        $task->type = $template->type;
        $task->status = 'Ongoing';
        $task->pages = $template->pages;
        $task->progress_percentage = 0.00;
        $task->save();

        $new_task_id = $task->id;

        $groupMembers = Link_tempo_group::where('task_id', $tasks)
        ->where('adder_id', Auth::user()->id)
        ->get();

        foreach($groupMembers as $groups){
            $group = new Task_group();
            $group->task_id = $new_task_id;
            $group->user_id = $groups->user_id;
            $group->save();
        }

        $pages = Task_templates_pages::where('template_id', $template->id)->get();
        $page_id_map = [];

        foreach ($pages as $page) {
            $new_pages = new Task_pages();
            $new_pages->task_id = $new_task_id;
            $new_pages->page_title = $page->page_title;
            $new_pages->page_content = $page->page_content;
            $new_pages->save();

            $page_id_map[$page->id] = $new_pages->id;
        }

        $fields = Task_templates_fields::where('template_id', $template->id)->get();

        foreach ($fields as $field) {
            $new_field = new Task_fields();
            $new_field->task_id = $new_task_id;
            $new_field->field_name = $field->field_name;
            $new_field->field_type = $field->field_type;
            $new_field->is_required = $field->is_required;
            $new_field->options = $field->options;
            $new_field->field_page = $page_id_map[$field->field_page] ?? null;
            $new_field->field_label = $field->field_label;
            $new_field->field_description = $field->field_description;
            $new_field->field_pre_answer = $field->field_pre_answer;
            $new_field->save();
        }

        $taskLinked = Task::find($tasks);
        $taskLinked->status = 'Linked';
        $taskLinked->save();

        foreach($groupMembers as $gnotif) {

            $notif = new Notification();
            $notif->user_id = $gnotif->user_id;
            $notif->message = 'New Assigned Group Task With Linked Task: '.$taskLinked->title.' ID: '.$tasks;
            $notif->type = 'info';
            $notif->save();

        }

        Link_tempo_group::where('task_id', $tasks)
        ->delete();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Assign Group Task: {$template->title} User: ".implode(", ", $profileNames)." With Linked Task: {$taskLinked->title} ID: {$tasks}";
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksLinkAndAssignOngoingTask(Request $request){
        $task = $request->task;
        $link = $request->link;

        // Check if linked task exists
        $checkFirst = Task::where('id', $link)->first();
        if (!$checkFirst) {
            return response()->json(['status' => 'error', 'message' => 'Distributed task not found']);
        }

        if ($checkFirst->status === 'Linked') {
            return response()->json(['status' => 'error', 'message' => 'Task is already linked']);
        }

        // Find main task
        $submit = Task::find($task);
        if (!$submit) {
            return response()->json(['status' => 'error', 'message' => 'Task to be linked not found']);
        }

        if($checkFirst->department_id !== $submit->department_id){
            $departmentName = Department::find($checkFirst->department_id)->name ?? 'another department';
            $departmentOngoing = Department::find($submit->department_id)->name ?? 'another department';
            return response()->json([
                'status' => 'error',
                'message' => "This task you want to link is for {$departmentName} and the ongoing task is on department {$departmentOngoing}, please try again and select same department"
            ]);
        }

        // Assign link ID
        $submit->link_id = $link;
        $submit->save();

        // Update linked task status
        $change = Task::find($link);
        if ($change) {
            $change->status = 'Linked';
            $change->save();
        }

        // Notifications
        if ($submit->type === 'Group') {
            $group = Task_group::where('task_id', $submit->id)->get();
            foreach ($group as $gnotif) {
                $notif = new Notification();
                $notif->user_id = $gnotif->user_id;
                $notif->message = 'Admin Linked Task: ' . $change->title . ' ID: ' . $link . ' To Your Task: ' . $submit->title;
                $notif->type = 'info';
                $notif->save();
            }
        } else if ($submit->type === 'Solo') {
            $solo = Task_solo::where('task_id', $submit->id)->first();
            if ($solo) { // Ensure $solo exists before using it
                $notif = new Notification();
                $notif->user_id = $solo->user_id;
                $notif->message = 'Admin: ' . Auth::user()->name . ' Linked Task: ' . $change->title . ' ID: ' . $link . ' To Your Task: ' . $submit->title;
                $notif->type = 'info';
                $notif->save();
            }
        }

        // Delete temporary link group entries
        Link_tempo_group::where('task_id', $link)->delete();

        // Log action
        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = 'Linked Task: ' . $change->title . ' ID: ' . $link . ' To Ongoing Task: ' . $submit->title;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success', 'message' => 'Task linked successfully']);
    }

    public function HeadTasksViewStatistic(Request $request){
        $task_id = $request->task;

        $task = Task::find($task_id);

        if($task){
            if($task->type === 'Solo'){
                $solo = Task_solo::where('task_id', $task->id)->first();

                if (!$solo) {
                    return response()->json(['status' => 'noSoloTask']);
                }

                $statuses = Task_user_status::where('task_id', $task_id)
                    ->where('user_id', $solo->user_id)
                    ->orderBy('created_at')
                    ->get();

                $user = User::find($solo->user_id);

                $totalDurations = [
                    'Active' => 0,
                    'Idle' => 0,
                    'Away' => 0,
                    'Emergency' => 0,
                    'Sleep' => 0,
                    'Overtime' => 0
                ];

                $statusDurations = [];
                $onlyEmergencyOrSleep = true;

                if ($statuses->isNotEmpty()) {
                    foreach ($statuses as $index => $status) {
                        $nextTimestamp = isset($statuses[$index + 1]) ? $statuses[$index + 1]->created_at : now();

                        $start = Carbon::parse($status->created_at);
                        $end = Carbon::parse($nextTimestamp);
                        $duration = $start->diffInSeconds($end);

                        if (array_key_exists($status->user_status, $totalDurations)) {
                            $totalDurations[$status->user_status] += $duration;
                        }

                        $statusDurations[] = [
                            'user_status' => $status->user_status,
                            'duration' => $duration // Store in seconds
                        ];

                        if ($status->user_status !== 'Emergency' && $status->user_status !== 'Sleep') {
                            $onlyEmergencyOrSleep = false;
                        }
                    }
                }

                $isParticipating = $statuses->isNotEmpty() && !$onlyEmergencyOrSleep;

                // Convert seconds to H:i:s format
                foreach ($totalDurations as $status => $seconds) {
                    $totalDurations[$status] = gmdate("H:i:s", $seconds);
                }


                return response()->json(['user' => $user, 'task' => $task, 'totalDuration' => $totalDurations, 'statuses' => $statusDurations, 'isParticipating' => $isParticipating, 'type' => 'Solo']);
            } else if($task->type === 'Group'){
                $groupMembers = Task_group::where('task_id', $task->id)->get();

                if ($groupMembers->isEmpty()) {
                    return response()->json(['status' => 'noGroupMembers']);
                }

                $groupData = [];

                foreach ($groupMembers as $group) {
                    $user = User::find($group->user_id);
                    if (!$user) {
                        continue; // Skip if user not found
                    }

                    $statuses = Task_user_status::where('task_id', $task_id)
                        ->where('user_id', $group->user_id)
                        ->orderBy('created_at')
                        ->get();

                    $totalDurations = [
                        'Active' => 0,
                        'Idle' => 0,
                        'Away' => 0,
                        'Emergency' => 0,
                        'Sleep' => 0,
                        'Overtime' => 0
                    ];

                    $statusDurations = [];
                    $onlyEmergencyOrSleep = true;

                    if ($statuses->isNotEmpty()) {
                        foreach ($statuses as $index => $status) {
                            $nextTimestamp = isset($statuses[$index + 1]) ? $statuses[$index + 1]->created_at : now();

                            $start = Carbon::parse($status->created_at);
                            $end = Carbon::parse($nextTimestamp);
                            $duration = $start->diffInSeconds($end);

                            if (array_key_exists($status->user_status, $totalDurations)) {
                                $totalDurations[$status->user_status] += $duration;
                            }

                            $statusDurations[] = [
                                'user_status' => $status->user_status,
                                'duration' => $duration // Store in seconds
                            ];

                            if ($status->user_status !== 'Emergency' && $status->user_status !== 'Sleep') {
                                $onlyEmergencyOrSleep = false;
                            }
                        }
                    }

                    $isParticipating = $statuses->isNotEmpty() && !$onlyEmergencyOrSleep;

                    // Convert total durations to readable format
                    foreach ($totalDurations as $status => $seconds) {
                        $totalDurations[$status] = gmdate("H:i:s", $seconds);
                    }

                    $groupData[] = [
                        'user' => $user,
                        'task' => $task,
                        'totalDuration' => $totalDurations,
                        'statuses' => $statusDurations,
                        'isParticipating' => $isParticipating
                    ];
                }

                return response()->json(['groupData' => $groupData, 'type' => 'Group']);
            }
        } else {
            return response()->json(['status' => 'notExist']);
        }
    }

    public function HeadTasksSetEmergencyUserStatus(Request $request){
        $task_id = $request->task;

        $task = Task::where('id', $task_id)->first();
        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found']);
        }

        if($task->user_status === 'Emergency'){
            return response()->json(['status' => 'error', 'message' => 'This task is already set to emergency']);
        }

        $task->user_status = 'Emergency';
        $task->save();

        if($task->type === 'Solo'){
            $solo = Task_solo::where('task_id', $task->id)->first();
            if(!$solo){
                return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
            }

            Task_user_status::create([
                'task_id' => $task->id,
                'user_id' => $solo->user_id,
                'user_status' => 'Emergency',
            ]);

            $notif = new Notification();
            $notif->user_id = $solo->user_id;
            $notif->message = 'Admin: ' . Auth::user()->name . ' Set Your Task: ' . $task->title . ' To Emergency';
            $notif->type = 'info';
            $notif->save();

            Log::create([
                'name' => Auth::user()->name,
                'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                'description' => now()->format('Y-m-d'),
            ]);

            return response()->json(['status' => 'success']);
        } else if($task->type == 'Group'){
            $group = Task_group::where('task_id', $task->id)->get();
            if($group->isEmpty()){
                return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
            }

            foreach($group as $member){
                Task_user_status::create([
                    'task_id' => $task->id,
                    'user_id' => $member->user_id,
                    'user_status' => 'Emergency',
                ]);

                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Admin: ' . Auth::user()->name . ' Set Your Group Task: ' . $task->title . ' To Emergency';
                $notif->type = 'info';
                $notif->save();
            }

            Log::create([
                'name' => Auth::user()->name,
                'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                'description' => now()->format('Y-m-d'),
            ]);

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown task type']);
    }

    public function HeadTasksAcceptOvertimeRequest(Request $request){
        $task_id = $request->task;

        $task = Task::where('id', $task_id)->where('user_status', 'Request Overtime')->whereIn('status', ['Overdue', 'Ongoing'])->first();
        if($task){
            if($task->type == "Group"){
                $group = Task_group::where('task_id', $task->id)->get();
                if($group->isEmpty()){
                    return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
                }
                if($group->isNotEmpty()){
                    foreach($group as $member){
                        $status = new Task_user_status();
                        $status->task_id = $task->id;
                        $status->user_id = $member->user_id;
                        $status->user_status = 'Overtime';
                        $status->save();

                        $notif = new Notification();
                        $notif->user_id = $member->user_id;
                        $notif->message = 'Admin: ' . Auth::user()->name . ' Accept Overtime Request Task: ' . $task->title ;
                        $notif->type = 'info';
                        $notif->save();

                        Log::create([
                            'name' => Auth::user()->name,
                            'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                            'description' => now()->format('Y-m-d'),
                        ]);
                    }
                }
            } else if($task->type == "Solo"){
                $solo = Task_solo::where('task_id', $task->id)->first();
                if(!$solo){
                    return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
                }
                if($solo){
                    $status = new Task_user_status();
                    $status->task_id = $task->id;
                    $status->user_id = $solo->user_id;
                    $status->user_status = 'Overtime';
                    $status->save();

                    $notif = new Notification();
                    $notif->user_id = $solo->user_id;
                    $notif->message = 'Admin: ' . Auth::user()->name . ' Accept Overtime Request Task: ' . $task->title ;
                    $notif->type = 'info';
                    $notif->save();

                    Log::create([
                        'name' => Auth::user()->name,
                        'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                        'description' => now()->format('Y-m-d'),
                    ]);
                }

            }
            $task->update(['user_status' => 'Overtime']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Task might be already accepted or declined for overtime check again']);
        }
        return response()->json(['status' => 'success']);
    }

    public function HeadTasksDeclineOvertimeRequest(Request $request){
        $task_id = $request->task;

        $task = Task::where('id', $task_id)->where('user_status', 'Request Overtime')->whereIn('status', ['Overdue', 'Ongoing'])->first();
        if($task){
            if($task->type == "Group"){
                $group = Task_group::where('task_id', $task->id)->get();
                if($group->isEmpty()){
                    return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
                }
                if($group->isNotEmpty()){
                    foreach($group as $member){
                        $status = new Task_user_status();
                        $status->task_id = $task->id;
                        $status->user_id = $member->user_id;
                        $status->user_status = 'Sleep';
                        $status->save();

                        $notif = new Notification();
                        $notif->user_id = $member->user_id;
                        $notif->message = 'Admin: ' . Auth::user()->name . ' Decline Overtime Request Task: ' . $task->title ;
                        $notif->type = 'info';
                        $notif->save();

                        Log::create([
                            'name' => Auth::user()->name,
                            'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                            'description' => now()->format('Y-m-d'),
                        ]);
                    }
                }
            } else if($task->type == "Solo"){
                $solo = Task_solo::where('task_id', $task->id)->first();
                if(!$solo){
                    return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
                }
                if($solo){
                    $status = new Task_user_status();
                    $status->task_id = $task->id;
                    $status->user_id = $solo->user_id;
                    $status->user_status = 'Sleep';
                    $status->save();

                    $notif = new Notification();
                    $notif->user_id = $solo->user_id;
                    $notif->message = 'Admin: ' . Auth::user()->name . ' Accept Overtime Request Task: ' . $task->title ;
                    $notif->type = 'info';
                    $notif->save();

                    Log::create([
                        'name' => Auth::user()->name,
                        'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                        'description' => now()->format('Y-m-d'),
                    ]);
                }
            }
            $task->update(['user_status' => 'Sleep']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Task might be already accepted or declined for overtime check again']);
        }
        return response()->json(['status' => 'success']);
    }

    public function HeadTasksUnlinkTask(Request $request){
        $task_id = $request->task;

        $task_to = Task::where('link_id', $task_id)->first();
        if(!$task_to){
            return response()->json(['status' => 'error', 'message' => 'Task not existing']);
        }
        $task_to->link_id = null;
        $task_to->save();

        $task = Task::find($task_id);
        if(!$task){
            return response()->json(['status' => 'error', 'message' => 'Task not existing']);
        }
        if($task->status === 'Distributed'){
            return response()->json(['status' => 'error', 'message' => 'Linked task might be already unlinked please check again']);
        }
        $task->status = 'Distributed';
        $task->save();

        if (!$task_to->type) {
            return response()->json(['status' => 'error', 'message' => 'Invalid task type']);
        }

        if($task_to->type === 'Solo'){
            $solo = Task_solo::where('task_id', $task_to->id)->first();
            if(!$solo){
                return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
            }

            $notif = new Notification();
            $notif->user_id = $solo->user_id;
            $notif->message = 'Admin: ' . Auth::user()->name . ' Unlink Task: ' . $task->title . ' To Your Task: '.$task_to->title;
            $notif->type = 'info';
            $notif->save();

            Log::create([
                'name' => Auth::user()->name,
                'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                'description' => now()->format('Y-m-d'),
            ]);

            return response()->json(['status' => 'success']);
        } else if($task_to->type == 'Group'){
            $group = Task_group::where('task_id', $task_to->id)->get();
            if($group->isEmpty()){
                return response()->json(['status' => 'error', 'message' => 'This task currently not holding by any user']);
            }

            foreach($group as $member){
                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Admin: ' . Auth::user()->name . ' Unlink Task: ' . $task->title . ' To Your Task: '.$task_to->title;
                $notif->type = 'info';
                $notif->save();
            }

            Log::create([
                'name' => Auth::user()->name,
                'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                'description' => now()->format('Y-m-d'),
            ]);

            return response()->json(['status' => 'success']);
        }
    }

    public function HeadTasksArchiveDistributedTask(Request $request){
        $task = Task::find($request->task);

        if(!$task){
            return response()->json(['status' => 'error', 'message' => 'This distributed task not existing might be linked already to other task or deleted already please check again']);
        }

        $task->is_archived = 1;
        $task->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Task Distributed Archived Task Title: {$task->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksArchiveCompletedTask(Request $request){
        $task = Task::find($request->task);

        if(!$task){
            return response()->json(['status' => 'error', 'message' => 'This distributed task not existing might be linked already to other task or deleted already please check again']);
        }

        if(!isset($request->linked)){
            if($task->link_id !== null){
                return response()->json(['status' => 'ask']);
            }
        }

        if(isset($request->linked) && $request->linked === 'Yes') {
            $linked = Task::find($task->link_id);

            if(!$linked){
                return response()->json(['status' => 'error', 'message' => 'This distributed task not existing might be linked already to other task or deleted already please check again']);
            }

            $linked->is_archived = 1;
            $linked->status = 'Distributed';
            $linked->save();

            $log_link = new Log();
            $log_link->name = Auth::user()->name;
            $log_link->action = "Task Linked Archived Task Title: {$linked->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
            $log_link->description = date('Y-m-d');
            $log_link->save();

        } else if(isset($request->linked) && $request->linked === 'No'){
            $linked = Task::find($task->link_id);

            if(!$linked){
                return response()->json(['status' => 'error', 'message' => 'This Linked task not existing please check again']);
            }

            if($linked->status === 'Distributed'){
                return response()->json(['status' => 'error', 'message' => 'Linked task might be already unlinked please check again']);
            }
            $linked->status = 'Distributed';
            $linked->save();

            $task->link_id = null;
            $task->save();
        }

        $task->is_archived = 1;
        $task->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Task Distributed Archived Task Title: {$task->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksRetrievetask(Request $request){
        $task = Task::find($request->task);

        if(!$task){
            return response()->json([
                'status' => 'error',
                'message' => 'This task might be deleted already please check again'
            ]);
        }

        $task->is_archived = 0;
        $task->save();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Task Retrieve.Task Title: {$task->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadTasksDeletetask(Request $request){
        $task = Task::find($request->task);

        if(!$task){
            return response()->json([
                'status' => 'error',
                'message' => 'This task might be deleted already please check again'
            ]);
        }

        if($task->link_id !== null){
            $linked = Task::find($task->link_id);

            if(!$linked){
                return response()->json(['status' => 'error', 'message' => 'This Linked task not existing please check again']);
            }

            if($linked->status === 'Distributed'){
                return response()->json(['status' => 'error', 'message' => 'Linked task might be already unlinked please check again']);
            }
            $linked->status = 'Distributed';
            $linked->save();
        }

        Task::where('id', $request->task)->delete();
        Task_pages::where('task_id', $request->task)->delete();
        Task_fields::where('task_id', $request->task)->delete();
        Task_inputs::where('task_id', $request->task)->delete();
        Task_solo::where('task_id', $request->task)->delete();
        Task_group::where('task_id', $request->task)->delete();

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "Task Delete. Task Title: {$task->title} User: ".Auth::user()->name." ID: ".Auth::user()->id;
        $log->description = date('Y-m-d');
        $log->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadChatSendTaskContactMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'attachments' => 'nullable|array|max:20', // Limit attachments to 20
            'attachments.*' =>  'file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,ico,tiff,psd,ai,eps,pdf,doc,docx,txt,ppt,pptx,xls,xlsx,odt,ods,odp,rtf,csv,zip,rar,7z|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            $errorMessages = collect($validator->errors()->all())->map(function ($error, $index) {
                return ($index + 1) . ". " . $error;
            })->implode("\n");

            return response()->json([
                'status' => 'error',
                'message' => "Oops! Some fields are missing or invalid:\n" . $errorMessages
            ]);
        }

        if($request->message === null && empty($request->attachments)){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! You can't leave attachments and message empty at the same time"
            ]);
        }

        $task_id = $request->task_id;
        $task = Task::find($task_id);

        if($task->type === 'Group'){
            $usersId = [];

            $group = Task_group::where('task_id', $task_id)
                            ->where('user_id', '!=', Auth::id())
                            ->get();

            foreach ($group as $row) {
                $usersId[] = (int)$row->user_id; // Ensure integer type
            }

            // Remove duplicates (including Auth::id() if it exists in task_groups)
            $usersId = array_unique($usersId);


            if($group->count() <= 1){
                $userId = Auth::id();
                $contactId = $userIds[0];
                if($contactId == $userId){
                    return response()->json([
                        'status' => 'error',
                        'message' => "Oops! You can't message yourself"
                    ]);
                }

                $chatId = ChatParticipant::select('chat_participants.chat_id')
                ->join('chats', 'chat_participants.chat_id', '=', 'chats.id') // Join the chats table
                ->where('chats.type', 'user_to_user') // Ensure the chat is user-to-user
                ->whereIn('chat_participants.user_id', [$userId, $contactId]) // Ensure both users are in the chat
                ->groupBy('chat_participants.chat_id')
                ->havingRaw('COUNT(DISTINCT chat_participants.user_id) = 2') // Ensure both users are in the same chat
                ->value('chat_participants.chat_id');

                // If no chat exists, create a new one
                if (!$chatId) {
                    $chat = Chat::create(['type' => 'user_to_user']);
                    $chatId = $chat->id;
                    ChatParticipant::create([
                        'chat_id' => $chatId,
                        'user_id' => $userId,
                    ]);
                    ChatParticipant::create([
                        'chat_id' => $chatId,
                        'user_id' => $contactId,
                    ]);

                    $notif = new Notification();
                    $notif->user_id = $contactId;
                    $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
                    $notif->type = 'info';
                    $notif->save();
                }

                // Create the message
                $data = [
                    'chat_id' => $chatId,
                    'user_id' => Auth::id(),
                    'message' => $request->input('message'),
                    'status' => 'sent',
                ];

                if (isset($request->task_id)) {
                    $data['task_id'] = $request->task_id;
                }

                $message = Message::create($data);

                // Handle attachments
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        // Validate file size (max 10MB)
                        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                        $uniqueId = uniqid();
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $uniqueId . '_' . $originalName . '.' . $extension;
                        $uploadPath = public_path('upload');
                        if (!file_exists($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }

                        // Move file first
                        $file->move($uploadPath, $fileName);

                        // Then get MIME type from the moved file
                        $filePath = $uploadPath . '/' . $fileName;
                        $mimeType = mime_content_type($filePath);

                        // Save the attachment details to the database
                        MessageAttachment::create([
                            'message_id' => $message->id,
                            'file_path' => 'upload/' . $fileName,
                            'file_type' => $mimeType,
                        ]);
                    }
                }

                foreach($usersId as $id){
                    $notif = new Notification();
                    $notif->user_id = $id;
                    $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
                    $notif->type = 'info';
                    $notif->save();
                }

                $user = User::find($contactId);
                return response()->json(['status' => 'success']);
            }

            $chatId = ChatParticipant::select('chat_participants.chat_id')
                ->join('chats', 'chat_participants.chat_id', '=', 'chats.id') // Join the chats table
                ->where('chats.type', 'group') // Ensure the chat is user-to-user
                ->whereIn('chat_participants.user_id', $usersId) // Ensure both users are in the chat
                ->groupBy('chat_participants.chat_id')
                ->havingRaw('COUNT(DISTINCT chat_participants.user_id) = ?', [count($usersId)]) // Ensure both users are in the same chat
                ->value('chat_participants.chat_id');

            if (!$chatId) {
                $chat = Chat::create(['type' => 'group']);
                $chatId = $chat->id;

                foreach ($usersId as $id) {
                    ChatParticipant::create([
                        'chat_id' => $chatId,
                        'user_id' => $id,
                    ]);

                    $notif = new Notification();
                    $notif->user_id = $id;
                    $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
                    $notif->type = 'info';
                    $notif->save();
                }

                ChatParticipant::create([
                    'chat_id' => $chatId,
                    'user_id' => Auth::id(),
                    'is_admin' => 1,
                    'is_creator' => 1
                ]);

                Message::create([
                    'chat_id' => $chatId,
                    'user_id' => Auth::id(),
                    'message' => 'create group.',
                    'status' => 'nickname',
                ]);

                Message::create([
                    'chat_id' => $chatId,
                    'task_id' => $task_id,
                    'user_id' => Auth::id(),
                    'message' => $request->input('message'),
                    'status' => 'sent',
                ]);

                return response()->json([
                    'status' => 'success'
                ]);
            }

            $data = [
                'chat_id' => $chatId,
                'task_id' => $task_id,
                'user_id' => Auth::id(),
                'message' => $request->input('message'),
                'status' => 'sent',
            ];

            $message = Message::create($data);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Validate file size (max 10MB)
                    $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    $uniqueId = uniqid();
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $uniqueId . '_' . $originalName . '.' . $extension;
                    $uploadPath = public_path('upload');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Move file first
                    $file->move($uploadPath, $fileName);

                    // Then get MIME type from the moved file
                    $filePath = $uploadPath . '/' . $fileName;
                    $mimeType = mime_content_type($filePath);

                    // Save the attachment details to the database
                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'file_path' => 'upload/' . $fileName,
                        'file_type' => $mimeType,
                    ]);
                }
            }

            foreach($usersId as $id){
                $notif = new Notification();
                $notif->user_id = $id;
                $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
                $notif->type = 'info';
                $notif->save();
            }

            return response()->json([
                'status' => 'success'
            ]);
        } else if($task->type === 'Solo'){
            $solo = Task_solo::where('task_id', $task_id)->first();
            $contactId = $solo->user_id;
            $userId = Auth::id();

            if($contactId == $userId){
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! You can't message yourself"
                ]);
            }

            $chatId = ChatParticipant::select('chat_participants.chat_id')
            ->join('chats', 'chat_participants.chat_id', '=', 'chats.id') // Join the chats table
            ->where('chats.type', 'user_to_user') // Ensure the chat is user-to-user
            ->whereIn('chat_participants.user_id', [$userId, $contactId]) // Ensure both users are in the chat
            ->groupBy('chat_participants.chat_id')
            ->havingRaw('COUNT(DISTINCT chat_participants.user_id) = 2') // Ensure both users are in the same chat
            ->value('chat_participants.chat_id');

            // If no chat exists, create a new one
            if (!$chatId) {
                $chat = Chat::create(['type' => 'user_to_user']);
                $chatId = $chat->id;
                ChatParticipant::create([
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                ]);
                ChatParticipant::create([
                    'chat_id' => $chatId,
                    'user_id' => $contactId,
                ]);

                $notif = new Notification();
                $notif->user_id = $contactId;
                $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
                $notif->type = 'info';
                $notif->save();
            }

            // Create the message
            $data = [
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'message' => $request->input('message'),
                'status' => 'sent',
            ];

            if (isset($request->task_id)) {
                $data['task_id'] = $request->task_id;
            }

            $message = Message::create($data);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Validate file size (max 10MB)
                    $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    $uniqueId = uniqid();
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $uniqueId . '_' . $originalName . '.' . $extension;
                    $uploadPath = public_path('upload');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Move file first
                    $file->move($uploadPath, $fileName);

                    // Then get MIME type from the moved file
                    $filePath = $uploadPath . '/' . $fileName;
                    $mimeType = mime_content_type($filePath);

                    // Save the attachment details to the database
                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'file_path' => 'upload/' . $fileName,
                        'file_type' => $mimeType,
                    ]);
                }
            }

            $notif = new Notification();
            $notif->user_id = $contactId;
            $notif->message = Auth::user()->name.' sent you a message about task '.$task->title;
            $notif->type = 'info';
            $notif->save();

            $user = User::find($contactId);
            return response()->json(['status' => 'success']);
        }
    }

//endregion

//region Calendar

    public function HeadCalendar(){
        return view('head.head_calendar');
    }

    public function HeadCalendarViewTaskDate(){
        $events = [];
        $task = Task::whereIn('status', ['Ongoing', 'Overdue'])->get();
        foreach($task as $row){
            $department = Department::find($row->department_id);
            $users = [];
            if($row->type === 'Solo'){
                $user = Task_solo::where('task_id', $row->id)->get();
                foreach($user as $persons){
                    $person = User::find($persons->user_id);
                    $users[] = array(
                        'user_id' => $person->id,
                        'name' => $person->name,
                        'photo' => $person->photo,
                    );
                }
            } else if($row->type === 'Group'){
                $user = Task_group::where('task_id', $row->id)->get();
                foreach($user as $persons){
                    $person = User::find($persons->user_id);
                    $users[] = array(
                        'user_id' => $person->id,
                        'name' => $person->name,
                        'photo' => $person->photo,
                    );
                }
            }
            $events[] = array(
                'id' => $row->id,
                'title' => $row->title,
                'department' => $department,
                'start' => $row->assigned,
                'end' => $row->due,
                'type' => $row->type,
                'percentage' => $row->progress_percentage,
                'status' => $row->status,
                'users' => $users,
                'my_id' => Auth::id()
            );
        }

        return response()->json($events);

    }

    public function HeadCalendarSaveEvent(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:225',
            'type' => 'required|string',
            'color' => ['required', 'string', 'regex:/^rgba?\(\s*\d{1,3},\s*\d{1,3},\s*\d{1,3}(,\s*\d?(\.\d+)?)?\s*\)$/'],
            'border' => ['required', 'string', 'regex:/^rgb\(\s*\d{1,3},\s*\d{1,3},\s*\d{1,3}\s*\)$/'],
            'start' => 'required|date|after_or_equal:now',
            'end' => 'nullable|date|after:start',
            'description' => 'nullable|string|max:1000',
        ], [
            'type.in' => 'Event type must be one of: meeting, reminder, task, holiday',
            'color.regex' => 'Color must be in RGB or RGBA format',
            'border.regex' => 'Border color must be in RGB format',
            'start.after_or_equal' => 'Start date must be in the future',
            'end.after' => 'End date must be after the start date'
        ]);

        if ($validator->fails()) {
            $errorMessages = collect($validator->errors()->all())->map(function ($error, $index) {
                return ($index + 1) . ". " . $error;
            })->implode("\n");

            return response()->json([
                'status' => 'error',
                'message' => "Oops! Some fields are missing or invalid:\n" . $errorMessages
            ]);
        }

        if($request->type === 'announcement'){
            $member = Member::where('user_id', Auth::user()->id)->first();
            $calendar = CalendarEvent::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'type' => $request->type,
                'start' => $request->start,
                'end' => $request->end,
                'color' => trim($request->color),
                'border' => trim($request->border),
                'description' => $request->description ?? null
            ]);

            $allUser = User::all();
            if($allUser->isNotEmpty()){
                foreach($allUser as $row){
                    if($row->id !== Auth::id()){
                        $notif = new Notification();
                        $notif->user_id = $row->id;
                        $notif->message = 'New Announcement Event posted in calendar by "'.Auth::user()->name.'" event title: "'.$request->title.'" date: "'.$request->start.'" kindly check it';
                        $notif->type = 'info';
                        $notif->save();
                    }
                }
            }

            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "User: ".Auth::user()->name." ID: ".Auth::user()->id . " create a announcement event";
            $log->description = date('Y-m-d');
            $log->save();

            Log::create([
                'name' => Auth::user()->name,
                'action' => "Create New Announcement Event in Calendar: {$request->title} ID: {$calendar->id}",
                'description' => now()->format('Y-m-d'),
            ]);

        } else {
            $calendar = CalendarEvent::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'type' => $request->type,
                'start' => $request->start,
                'end' => $request->end,
                'color' => trim($request->color),
                'border' => trim($request->border),
                'description' => $request->description ?? null
            ]);
        }


        return response()->json([
            'status' => 'success',
            'id' => $calendar->id
        ]);
    }

    public function HeadCalendarViewPrivateEventDate(){
        $event = CalendarEvent::where('user_id', Auth::id())->where('type', 'private')->with('user')->get();

        if($event->isNotEmpty()){
            return response()->json($event);
        } else {
            return response()->json([
                'status' => 'nothing'
            ]);
        }
    }

    public function HeadCalendarViewDepartmentEventDate(){
        $member = Member::where('user_id', Auth::user()->id)->first();
        $event = CalendarEvent::where('type', 'department')->with('user')->get();

        if($event->isNotEmpty()){
            return response()->json($event);
        } else {
            return response()->json([
                'status' => 'nothing'
            ]);
        }
    }

    public function HeadCalendarViewAnnouncementEventDate(){
        $event = CalendarEvent::where('type', 'announcement')->with('user')->get();

        if($event->isNotEmpty()){
            return response()->json($event);
        } else {
            return response()->json([
                'status' => 'nothing'
            ]);
        }
    }

    public function HeadCalendarRemoveEvent(Request $request){
        $calendar = CalendarEvent::where('id', $request->event)->first();
        if(!$calendar){
            return response()->json([
                'status' => 'error',
                'message' => 'This event must be removed, please check again'
            ]);
        }

        if($calendar->type == 'department'){
            Log::create([
                'name' => Auth::user()->name,
                'action' => "Delete Department Event in Calendar: {$calendar->title} ID: {$calendar->id}",
                'description' => now()->format('Y-m-d'),
            ]);
        }

        if($calendar->type == 'announcement'){
            Log::create([
                'name' => Auth::user()->name,
                'action' => "Delete Company Event in Calendar: {$calendar->title} ID: {$calendar->id}",
                'description' => now()->format('Y-m-d'),
            ]);
        }

        $calendar->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }

//endregion

//region Department
    public function HeadDepartment(){
        $dept = Department::all();

        $dept->each(function($member) {
            $member->head_name = Member::where('department_id', $member->id)
            ->where('role', 'observer')
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->first();

            $member->dept_member = Member::where('department_id', $member->id)
            ->whereIn('role', ['employee', 'intern'])
            ->get();

            $member->dept_head = Member::where('department_id', $member->id)
            ->where('role', 'observer')
            ->get();

            $member->task = Task::where('department_id', $member->id)->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])->get();
        });

        return view('head.head_department', compact('dept'));
    }

    public function HeadAddDepartment(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string'],
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }


        $check = Department::where('name', $request->name)->first();
        if($check){
            return response()->json(['status' => 'nameExist']);
        }


        $department = new Department();
        $department->name = $request->input('name', null);
        $department->created_by = Auth::user()->name;

        $department->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadGetUserDepartments(){
        $user = User::whereIn('role', ['Employee', 'Observer', 'Intern'])
        ->whereNotIn('id', function ($query) {
            $query->select('user_id')->from('members');
        })
        ->get();
        return response()->json($user);
    }

    public function HeadGetDepartmentHead(){
        $id = $_GET['id'];
        $member = Member::where('department_id', $id)->where('role', 'Observer')->get();
        $output = [];
        if(!empty($member)){
            foreach($member as $user){
                $photoUrl = !empty($user->profile)
                ? url('upload/photo_bank/' . $user->profile)
                : url('upload/nophoto.jfif');

                $output[] = [
                    '<img src="'.$photoUrl.'" alt="image">',
                    $user->name,
                    '<button type="button" id="removeHead" data-user="'.$user->user_id.'" data-dept="'.$user->department_id.'" class="btn btn-primary">Remove</button>'
                ];
            }
        }

        return response()->json(['list' => $output]);
    }

    public function HeadGetDepartmentMember(){
        $id = $_GET['id'];
        $member = Member::where('department_id', $id)
        ->whereIn('role', ['Employee', 'Intern'])
        ->get();
        $output = [];
        if(!empty($member)){
            foreach($member as $user){
                $photoUrl = !empty($user->profile)
                ? url('upload/photo_bank/' . $user->profile)
                : url('upload/nophoto.jfif');


                $output[] = [
                    '<img src="'.$photoUrl.'" alt="image">',
                    $user->name,
                    '<button type="button" id="removeMember" data-user="'.$user->user_id.'" data-dept="'.$user->department_id.'" class="btn btn-primary">Remove</button>'
                ];
            }
        }

        return response()->json(['list' => $output]);
    }

    public function HeadGetDepartmentVHead(){
        $id = $_GET['id'];
        $member = Member::where('department_id', $id)->with('user')->where('role', 'Observer')->get();
        $output = [];
        if(!empty($member)){
            foreach($member as $user){
                $photoUrl = !empty($user->profile)
                ? url('upload/photo_bank/' . $user->profile)
                : url('upload/nophoto.jfif');

                $output[] = [
                    '<img src="'.$photoUrl.'" alt="image">',
                    $user->name,
                    '<button type="button" id="vHeadDept" data-user="'.$user->user_id.'" data-dept="'.$user->department_id.'" class="btn btn-primary">View</button> <button type="button" id="mHeadDept" data-user="'.$user->user_id.'" class="btn btn-success chatWithUser"  data-name="'.$user->user->name.'" data-user="'.$user->user_id.'">Message</button>'
                ];
            }
        }

        return response()->json(['list' => $output]);
    }

    public function HeadGetDepartmentVMember(){
        $id = $_GET['id'];
        $member = Member::where('department_id', $id)
        ->with('user')
        ->whereIn('role', ['Employee', 'Intern'])
        ->get();
        $output = [];
        if(!empty($member)){
            foreach($member as $user){
                $photoUrl = !empty($user->profile)
                ? url('upload/photo_bank/' . $user->profile)
                : url('upload/nophoto.jfif');


                $output[] = [
                    '<img src="'.$photoUrl.'" alt="image">',
                    $user->name,
                    '<button type="button" id="vMemberDept" data-user="'.$user->user_id.'" data-dept="'.$user->department_id.'" class="btn btn-primary">View</button> <button type="button" id="mMemberDept" data-user="'.$user->user_id.'"  class="btn btn-success chatWithUser"  data-name="'.$user->user->name.'" data-user="'.$user->user_id.'">Message</button>',

                ];
            }
        }

        return response()->json(['list' => $output]);
    }

    public function HeadGetDepartmentEDept(){
        $id = $_GET['id'];
        $dept = Department::where('id', $id)->first();

        return response()->json($dept);
    }

    public function HeadGetDepartmentVUser(){
        $user = $_GET['user'];
        $dept = $_GET['dept'];

        $department = Department::where('id', $dept)->first();
        $acc = User::where('id', $user)->first();

        if (!$department || !$acc) {
            return response()->json(['output' => '<div>No data found for the given user or department.</div>']);
        }

        $photo = !empty($acc->photo)
        ? url('upload/photo_bank/' . $acc->photo)
        : url('upload/nophoto.jfif');

        $output = "
        <div class=\"row\">
            <div class=\"col-md-4 mb-3\">
                <div class=\"card card-img rounded-3 div-hover\">
                    <div class=\"card-body card-top rounded-top\">
                        <img class=\"card-photo rounded-3 w-100 shadow\" src=\"{$photo}\" alt=\"Profile Photo\" id=\"showImage\">
                    </div>
                </div>
            </div>
            <div class=\"col-md-8 mb-3\">
                <h6 class=\"card-title\">Profile Information</h6>
                <div class=\"mb-3\">
                    <label for=\"deptName\" class=\"form-label\">Department</label>
                    <input type=\"text\" name=\"deptName\" class=\"form-control\" id=\"deptName\" value=\"{$department->name}\" disabled>
                </div>
                <div class=\"mb-3\">
                    <label for=\"username\" class=\"form-label\">Name</label>
                    <input type=\"text\" class=\"form-control\" id=\"username\" value=\"{$acc->name}\" disabled>
                </div>
                <div class=\"mb-3\">
                    <label for=\"email\" class=\"form-label\">Email Address</label>
                    <input type=\"email\" name=\"email\" class=\"form-control\" id=\"email\" value=\"{$acc->email}\" disabled>
                </div>
                <div class=\"mb-3\">
                    <label for=\"phoneNumber\" class=\"form-label\">Phone Number</label>
                    <input type=\"text\" name=\"phoneNumber\" class=\"form-control\" id=\"phoneNumber\" value=\"{$acc->phone}\" disabled>
                </div>
            </div>
        </div>";


        return response()->json(['output' => $output]);
    }

    public function HeadGetDepartmentMUser(){
        $user = $_GET['user'];

        $acc = User::where('id', $user)->first();

        $photo = !empty($acc->photo)
        ? url('upload/photo_bank/' . $acc->photo)
        : url('upload/nophoto.jfif');

        $output = "
        <div class=\"d-flex justify-content-between align-items-center pb-2 mb-2\">
            <div class=\"d-flex align-items-center\">
                <figure class=\"me-2 mb-0\">
                    <img src=\"{$photo}\" class=\"img-sm border border-success rounded-circle\" alt=\"profile\">
                </figure>
                <div>
                    <h6>{$acc->name}</h6>
                </div>
            </div>
        </div>
        <div class=\"chat-content\">
            <div class=\"chat-footer d-flex\">
                <div class=\"d-none d-md-block\">
                    <button type=\"button\" class=\"btn border btn-icon rounded-circle me-2\" data-bs-toggle=\"tooltip\" data-bs-title=\"Attatch files\">
                        <i data-feather=\"paperclip\" class=\"text-muted icon-wiggle\"></i>
                    </button>
                </div>
                <form class=\"search-form flex-grow-1\">
                    <div class=\"input-group\">
                        <textarea type=\"text\" class=\"form-control rounded-2\" id=\"chatForm\" placeholder=\"Type a message\" rows=\"4\"></textarea>
                    </div>
                </form>
            </div>
        </div>
        ";

        return response()->json(['output' => $output]);
    }

    public function HeadEditDepartment(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string'],
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }

        $check = Department::where('name', $request->name)->first();
        if($check){
            return response()->json(['status' => 'nameExist']);
        }

        $department = Department::find($request->id);
        if (!$department) {
            return response()->json(['status' => 'notFound', 'message' => 'Department not found.']); // Handle missing department
        }

        $department->name = $request->name;

        $department->save();

        return response()->json(['status' => 'success', 'message' => 'Department updated successfully.']);
    }

    public function HeadDepartmentRDept(Request $request){
        $check = Member::where('department_id', $request->id)->exists();
        if($check){
            return response()->json(['status' => 'cannotRemove']);
        }

        $department = Department::find($request->id); // Find the department by its id

        if ($department) {
            $department->delete(); // Delete the department
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'notFound']);
    }

    public function HeadAssignDepartmentMember(Request $request){
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'required|exists:users,id', // Ensure user_id exists as id in Users table
        ]);

        $info = User::find($request->user_id);

        if (!$info) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $member = new Member();
        $member->department_id = $request->department_id;
        $member->user_id = $request->user_id;
        $member->profile = $info->photo;
        $member->name = $info->name;
        $member->role = $info->role;


        $member->save();

        return response()->json(['status' => 'success']);
    }

    public function HeadRemoveDepartmentMember(Request $request){
        $member = Member::where('user_id', $request->user_id)->first();

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found.'
            ]);
        }

        // Check for active solo tasks
        $hasActiveSoloTasks = Task_solo::where('user_id', $request->user_id)
            ->whereHas('task', function($query) {
                $query->whereIn('status', ['Ongoing', 'To Check', 'Overdue']);
            })
            ->exists();

        // Check for active group tasks
        $hasActiveGroupTasks = Task_group::where('user_id', $request->user_id)
            ->whereHas('task', function($query) {
                $query->whereIn('status', ['Ongoing', 'To Check', 'Overdue']);
            })
            ->exists();

        if ($hasActiveSoloTasks || $hasActiveGroupTasks) {
            return response()->json([
                'status' => 'error',
                'message' => 'This member has active tasks assigned and cannot be removed.'
            ]);
        }

        $deleteResult = $member->delete();

        if (!$deleteResult) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove member.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Member removed successfully.'
        ]);
    }
//endregion

//region Chat
    public function HeadChat(){
        $users = User::where('id', '!=', Auth::id())->with('department')->get();

        $userId = Auth::id();
        $chats = ChatParticipant::where('chat_participants.user_id', $userId)
        ->join('chats', 'chats.id', '=', 'chat_participants.chat_id')
        ->leftJoin('messages', function ($join) {
            $join->on('messages.chat_id', '=', 'chats.id')
                ->whereRaw('messages.created_at = (SELECT MAX(created_at) FROM messages WHERE messages.chat_id = chats.id)');
        })
        ->select('chat_participants.chat_id', 'chats.type', 'chats.photo', 'chats.name', 'messages.created_at as last_message_time')
        ->orderBy('messages.created_at', 'desc') // Sort by last message time
        ->get();

        // Process each chat
        $chatResult = [];
        foreach ($chats as $chat) {
            $chatId = $chat->chat_id;
            $chatType = $chat->type;
            $chatName = $chat->name;
            $chatPhoto = '';
            $convoPhoto = $chat->photo;
            $isOnline = false;

            if ($chatType === 'user_to_user') {
                // For user-to-user chats, get the other participant's name, photo, and online status
                $otherParticipant = ChatParticipant::where('chat_id', $chatId)
                    ->where('user_id', '!=', $userId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->first(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $chatName = !empty($chat->name) ? $chat->name : ($otherParticipant->nickname ?: ($otherParticipant->name ?? 'Unknown User'));
                $chatPhoto = $otherParticipant->photo; // Default photo if none
                $isOnline = $otherParticipant->is_online ?? false; // Default to false if none
            } elseif ($chatType === 'group') {
                // For group chats with no name, get all participants' names, photos, and online status
                $participants = ChatParticipant::where('chat_id', $chatId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->get(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $participantNames = $participants->map(function ($participant) {
                    return $participant->nickname ?: ($participant->name ?? 'Unknown User');
                })->toArray();

                $chatName = !empty($chat->name) ? $chat->name : implode(', ', $participantNames);

                // Get the first participant's photo as the group chat photo (or use a default)
                $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
                $chatPhoto = $participantPhotos;

                // Check if any participant is online
                $isOnline = $participants->contains('is_online', true);
            }

            // Get the count of messages not seen by the user
            $unseenCount = Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $userId) // Messages sent by others
                ->whereIn('status', ['sent', 'delivered']) // Not seen yet
                ->count();

            // Get the last message in the chat
            $lastMessage = Message::where('chat_id', $chatId)
                ->orderBy('created_at', 'desc')
                ->first(['message', 'created_at', 'id', 'is_unsend']);

            $hasImageAttachment = true;
            if ($lastMessage) {
                $hasImageAttachment = MessageAttachment::where('message_id', $lastMessage->id)
                    ->exists(); // Check if an image attachment exists
            }

            $lastMessageFrom = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->first(['user_id']);

            // Format the last message time using Carbon
            $lastMessageTime = $lastMessage
                ? Carbon::parse($lastMessage->created_at)->diffForHumans() // e.g., "1 sec ago"
                : null;

            // Add the chat to the result
            $chatResult[] = [
                'chat_id' => $chatId,
                'type' => $chatType,
                'name' => $chatName,
                'convo_photo' => $convoPhoto,
                'photo' => $chatPhoto, // Photo of the other participant or group
                'is_online' => $isOnline, // Online status of the other participant or group
                'unseen_count' => $unseenCount, // Number of unseen messages
                'last_message' => $lastMessage ? $lastMessage->message : null, // Last message text
                'from_message' => $lastMessageFrom->user_id,
                'is_attached' => $hasImageAttachment,
                'last_message_time' => $lastMessageTime, // Last message time in "time ago" format
                'last_message_actual_time' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                'is_unsend' => $lastMessage->is_unsend,
            ];
        }

        $rooms = Meeting::where('is_active', 1)
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_id' => $room->room_id,
                    'room_url' => $room->room_url, // Add this line
                    'room_name' => $room->room_name,
                    'description' => $room->description,
                    'created_at' => $room->created_at->format('M d, Y H:i'),
                    'created_at_human' => $room->created_at->diffForHumans(),
                    'created_by_id' => $room->user_id,
                    'is_creator' => $room->user_id === Auth::id(),
                    'user_info' => [
                        'id' => $room->user->id,
                        'name' => $room->user->name,
                        'email' => $room->user->email,
                        'avatar' => $room->user->photo
                    ]
                ];
            });

        $roomsEnd = Meeting::where('is_active', 0)
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_id' => $room->room_id,
                    'room_url' => $room->room_url, // Add this line
                    'room_name' => $room->room_name,
                    'description' => $room->description,
                    'created_at' => $room->created_at->format('M d, Y H:i'),
                    'updated_at' => $room->updated_at->format('M d, Y H:i'),
                    'created_at_human' => $room->created_at->diffForHumans(),
                    'created_by_id' => $room->user_id,
                    'is_creator' => $room->user_id === Auth::id(),
                    'user_info' => [
                        'id' => $room->user->id,
                        'name' => $room->user->name,
                        'email' => $room->user->email,
                        'avatar' => $room->user->photo
                    ]
                ];
            });


        return view('head.head_chat', compact('users', 'chatResult', 'rooms', 'roomsEnd'));
    }

    public function HeadChatSendContactMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'attachments' => 'nullable|array|max:20', // Limit attachments to 20
            'attachments.*' =>  'file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,ico,tiff,psd,ai,eps,pdf,doc,docx,txt,ppt,pptx,xls,xlsx,odt,ods,odp,rtf,csv,zip,rar,7z|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            $errorMessages = collect($validator->errors()->all())->map(function ($error, $index) {
                return ($index + 1) . ". " . $error;
            })->implode("\n");

            return response()->json([
                'status' => 'error',
                'message' => "Oops! Some fields are missing or invalid:\n" . $errorMessages
            ]);
        }

        if($request->message === null && empty($request->attachments)){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! You can't leave attachments and message empty at the same time"
            ]);
        }

        $contactId = $request->contact_id;
        $userId = Auth::id();

        $chatId = ChatParticipant::select('chat_participants.chat_id')
        ->join('chats', 'chat_participants.chat_id', '=', 'chats.id') // Join the chats table
        ->where('chats.type', 'user_to_user') // Ensure the chat is user-to-user
        ->whereIn('chat_participants.user_id', [$userId, $contactId]) // Ensure both users are in the chat
        ->groupBy('chat_participants.chat_id')
        ->havingRaw('COUNT(DISTINCT chat_participants.user_id) = 2') // Ensure both users are in the same chat
        ->value('chat_participants.chat_id');

        // If no chat exists, create a new one
        if (!$chatId) {
            $chat = Chat::create(['type' => 'user_to_user']);
            $chatId = $chat->id;
            ChatParticipant::create([
                'chat_id' => $chatId,
                'user_id' => $userId,
            ]);
            ChatParticipant::create([
                'chat_id' => $chatId,
                'user_id' => $contactId,
            ]);

            $notif = new Notification();
            $notif->user_id = $contactId;
            $notif->message = Auth::user()->name.' sent you a message';
            $notif->type = 'info';
            $notif->save();
        }

        // Create the message
        $data = [
            'chat_id' => $chatId,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
            'status' => 'sent',
        ];

        if (isset($request->task_id)) {
            $data['task_id'] = $request->task_id;
        }

        $message = Message::create($data);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Validate file size (max 10MB)
                $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                $uniqueId = uniqid();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileName = $uniqueId . '_' . $originalName . '.' . $extension;
                $uploadPath = public_path('upload');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file first
                $file->move($uploadPath, $fileName);

                // Then get MIME type from the moved file
                $filePath = $uploadPath . '/' . $fileName;
                $mimeType = mime_content_type($filePath);

                // Save the attachment details to the database
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => 'upload/' . $fileName,
                    'file_type' => $mimeType,
                ]);
            }
        }

        $user = User::find($contactId);
        return response()->json(['status' => 'success', 'message' => $user->name]);
    }

    public function reloadChatList(Request $request){
        // Retrieve all tasks from the database.
        $userId = Auth::id();

        // Fetch the chats where the user is a participant, sorted by the last message time
        $chats = ChatParticipant::where('chat_participants.user_id', $userId)
            ->join('chats', 'chats.id', '=', 'chat_participants.chat_id')
            ->leftJoin('messages', function ($join) {
                $join->on('messages.chat_id', '=', 'chats.id')
                    ->whereRaw('messages.created_at = (SELECT MAX(created_at) FROM messages WHERE messages.chat_id = chats.id)');
            })
            ->select('chat_participants.chat_id', 'chats.type', 'chats.photo', 'chats.name', 'messages.created_at as last_message_time')
            ->orderBy('messages.created_at', 'desc') // Sort by last message time
            ->get();

        // Process each chat
        $chatResult = [];
        foreach ($chats as $chat) {
            $chatId = $chat->chat_id;
            $chatType = $chat->type;
            $chatName = $chat->name;
            $chatPhoto = ''; // Default photo
            $convoPhoto = $chat->photo;

            $isOnline = false; // Default to false

            if ($chatType === 'user_to_user') {
                // For user-to-user chats, get the other participant's name, photo, and online status
                $otherParticipant = ChatParticipant::where('chat_id', $chatId)
                    ->where('user_id', '!=', $userId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->first(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $chatName = $chatName != '' ? $chatName : ($otherParticipant->nickname ?: ($otherParticipant->name ?? 'Unknown User'));
                $chatPhoto = $otherParticipant->photo;
                $isOnline = $otherParticipant->is_online ?? false; // Default to false if none
            } elseif ($chatType === 'group') {
                // For group chats with no name, get all participants' names, photos, and online status
                $participants = ChatParticipant::where('chat_id', $chatId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->get(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

                $participantNames = $participants->map(function ($participant) {
                    return $participant->nickname ?: ($participant->name ?? 'Unknown User');
                })->toArray();

                $chatName = $chatName != '' ? $chatName : implode(', ', $participantNames);

                // Get the photos of the first 2 participants
                $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
                $chatPhoto = $participantPhotos; // Store the array of photos

                // Check if any participant is online
                $isOnline = $participants->contains('is_online', true);
            }

            // Get the count of messages not seen by the user
            $unseenCount = Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $userId) // Messages sent by others
                ->whereIn('status', ['sent', 'delivered']) // Not seen yet
                ->count();

            // Get the last message in the chat
            $lastMessage = Message::where('chat_id', $chatId)
                ->orderBy('created_at', 'desc')
                ->first(['message', 'created_at', 'id', 'user_id', 'is_unsend', 'updated_at']);

            // Check if the last message has an image attachment
            $hasImageAttachment = false;
            if ($lastMessage) {
                $hasImageAttachment = MessageAttachment::where('message_id', $lastMessage->id)
                    ->where('file_type', 'like', 'image%') // Check for image attachments
                    ->exists();
            }

            // Get the user ID of the last message sender
            $lastMessageFrom = $lastMessage ? $lastMessage->user_id : null;

            // Format the last message time using Carbon
            $lastMessageTime = $lastMessage
                ? Carbon::parse($lastMessage->created_at)->diffForHumans() // e.g., "1 sec ago"
                : null;

            // Add the chat to the result
            $chatResult[] = [
                'chat_id' => $chatId,
                'type' => $chatType,
                'name' => $chatName,
                'convo_photo' => $convoPhoto,
                'photo' => $chatPhoto, // Photo of the other participant or group
                'is_online' => $isOnline, // Online status of the other participant or group
                'unseen_count' => $unseenCount, // Number of unseen messages
                'last_message' => $lastMessage ? $lastMessage->message : null, // Last message text
                'from_message' => $lastMessageFrom, // ID of the user who sent the last message
                'is_attached' => $hasImageAttachment, // Whether the last message has an image attachment
                'last_message_time' => $lastMessageTime, // Last message time in "time ago" format
                'auth_id' => Auth::id(),
                'last_message_actual_time' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                'is_unsend', $lastMessage->is_unsend,
                'last_message_updated_at' => $lastMessage->updated_at
            ];
        }
        // Build an associative array for current tasks: [id => updated_at]
        $currentSnapshot = [];
        foreach ($chatResult as $chat) {
            $currentSnapshot[$chat['chat_id']] =[
                'updated_at' => $chat['last_message_updated_at'],
                'time' => $chat['last_message_time']
            ];
        }

        // Decode the client's last update data (expected as an associative array).
        $clientSnapshot = json_decode($request->lastUpdate, true);

        // If no client data was provided, treat this as the initial load.
        if (!$clientSnapshot || !is_array($clientSnapshot)) {
            return response()->json([
                'status'     => 'initial_load',
                'lastUpdate' => $currentSnapshot,
                'chats'      => $chatResult
            ]);
        }


        // Check if the number of tasks differs.
        if (count($currentSnapshot) !== count($clientSnapshot)) {
            // Find new chats (chats in $currentSnapshot but not in $clientSnapshot)
            $newChats = [];
            foreach ($currentSnapshot as $chatId => $lastMessageTime) {
                if (!isset($clientSnapshot[$chatId])) {
                    $newChat = collect($chatResult)->firstWhere('chat_id', $chatId);
                    if ($newChat) {
                        $newChats[] = $newChat;
                    }
                }
            }

            // Find deleted chats (chats in $clientSnapshot but not in $currentSnapshot)
            $deletedChatIds = [];
            foreach ($clientSnapshot as $chatId => $lastMessageTime) {
                if (!isset($currentSnapshot[$chatId])) {
                    $deletedChatIds[] = $chatId;
                }
            }

            return response()->json([
                'status'          => 'count_changed',
                'lastUpdate'      => $currentSnapshot,
                'newChats'        => $newChats,       // New chats to add
                'deletedChatIds'  => $deletedChatIds  // Chat IDs to remove
            ]);
        }

        foreach ($currentSnapshot as $chatId => $currentData) {
            if (!isset($clientSnapshot[$chatId])) {
                $updatedChat = collect($chatResult)->firstWhere('chat_id', $chatId);
                if ($updatedChat) {
                    return response()->json([
                        'status'     => 'chat_updated',
                        'lastUpdate' => $currentSnapshot,
                        'chat'       => $updatedChat // Return only the updated chat
                    ]);
                }
            }
            $clientData = $clientSnapshot[$chatId];

            if ($clientData['time'] !== $currentData['time'] ||
            $clientData['updated_at'] !== $currentData['updated_at']) {
                $updatedChat = collect($chatResult)->firstWhere('chat_id', $chatId);
                if ($updatedChat) {
                    return response()->json([
                        'status'     => 'chat_updated',
                        'lastUpdate' => $currentSnapshot,
                        'chat'       => $updatedChat // Return only the updated chat
                    ]);
                }
            }
        }

        // No changes detected.
        return response()->json([
            'status'     => 'no_changes',
            'lastUpdate' => $currentSnapshot,
            'chats'      => $chatResult
        ]);
    }

    public function HeadViewChats(Request $request){
        $chat_id = $request->chat;
        $page = $request->page ?? 1; // Get page number from request
        $perPage = 20; // Messages per page

        $chat = Chat::find($chat_id);
        if(!$chat){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This conversation is not existing, it might be deleted please check again."
            ]);
        }

        $convoPhoto = $chat->photo;

        $chatPart = ChatParticipant::where('chat_id', $chat_id)->where('user_id', Auth::id())->first();
        if(!$chatPart){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! You can't access this conversation."
            ]);
        } else {
            ChatParticipant::where('user_id', Auth::id())->where('chat_id', '!=', $chat_id)->where('is_here', 1)->update(['is_here' => 0]);
            $chatPart->is_here = 1;
            $chatPart->save();
        }

        $otherPart = [];
        if ($chat->type === 'user_to_user') {
            // For user-to-user chats, get the other participant's name, photo, and online status
            $otherParticipant = ChatParticipant::where('chat_id', $chat_id)
                ->where('user_id', '!=', Auth::id())
                ->join('users', 'users.id', '=', 'chat_participants.user_id')
                ->first(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

            $chatName = !empty($chat->name) ? $chat->name : ($otherParticipant->nickname ?: ($otherParticipant->name ?? 'Unknown User'));
            $chatPhoto = $otherParticipant->photo; // Default photo if none
            $isOnline = $otherParticipant->is_online ?? false; // Default to false if none

            $otherPart[] = [
                'other_name' => $chatName,
                'other_photo' => $chatPhoto,
                'other_online' => $isOnline,
                'type' => $chat->type
            ];
        } elseif ($chat->type === 'group') {
            // For group chats with no name, get all participants' names, photos, and online status
            $participants = ChatParticipant::where('chat_id', $chat_id)
                ->join('users', 'users.id', '=', 'chat_participants.user_id')
                ->get(['chat_participants.nickname', 'users.name', 'users.photo', 'users.is_online']);

            // Prioritize nickname, then name, then "Unknown User"
            $participantNames = $participants->map(function ($participant) {
                return $participant->nickname ?: ($participant->name ?? 'Unknown User');
            })->toArray();

            // Get the first participant's photo as the group chat photo (or use a default)
            $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
            $chatPhoto = $participantPhotos;

            // Check if any participant is online
            $isOnline = $participants->contains('is_online', true);

            $otherPart[] = [
                'other_name' => !empty($chat->name) ? $chat->name : (implode(', ', $participantNames)), // Join names for display
                'other_photo' => $chatPhoto,
                'other_online' => $isOnline,
                'type' => $chat->type
            ];
        }

        $pinnedMessages = [];
        $getPinned = PinnedMessage::where('chat_id', $chat_id)
        ->orderBy('id', 'desc') // Assuming 'id' is incremental
        ->first();
        if($getPinned){
            $pinVal = Message::find($getPinned->message_id);
            $pinnedUser = User::find($pinVal->user_id);

            $chatParticipant = ChatParticipant::where('chat_id', $chat_id)
                ->where('user_id', $pinVal->user_id)
                ->first();

            $userName = $chatParticipant && !empty($chatParticipant->nickname)
                ? $chatParticipant->nickname
                : ($pinnedUser->name ?? 'Unknown');

            $pinnedTime = Carbon::parse($getPinned->created_at);
            if ($pinnedTime->isToday('Asia/Manila')) {
                $formattedpinnedTime = $pinnedTime->format('h:i A'); // e.g., "02:30 PM"
            } else {
                $formattedpinnedTime = $pinnedTime->format('h:i A, M d'); // e.g., "02:30 PM, Aug 10"
            }

            $pinnedMessages[] = [
                'chat_id' => $chat_id,
                'message_id' => $pinVal->id,
                'user_id' => $pinVal->user_id,
                'message' =>  $pinVal->message,
                'created_at' => $formattedpinnedTime,
                'photo' => $pinnedUser->photo ?? null,
                'user_name' => $userName
            ];
        }

        $currentUserId = Auth::id();

        // Retrieve message IDs that need to be marked as seen
        $messageIds = Message::where('chat_id', $chat_id)
            ->where('user_id', '!=', $currentUserId)
            ->whereIn('status', ['sent', 'delivered', 'seen'])
            ->pluck('id');

        if ($messageIds->isNotEmpty()) {
            // Update status for relevant messages
            Message::whereIn('id', $messageIds)
                ->whereIn('status', ['sent', 'delivered'])
                ->update(['status' => 'seen']);

            // Get existing seen records
            $existingSeen = SeenMessage::where('user_id', $currentUserId)
                ->whereIn('message_id', $messageIds)
                ->pluck('message_id')
                ->toArray();

            // Find new message IDs that need to be marked as seen
            $newMessageIds = array_diff($messageIds->toArray(), $existingSeen);

            if (!empty($newMessageIds)) {
                $now = Carbon::now();

                // Bulk insert with timestamps
                $seenData = array_map(function ($messageId) use ($currentUserId, $now) {
                    return [
                        'message_id' => $messageId,
                        'user_id' => $currentUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $newMessageIds);

                // Use transaction for atomic operations
                DB::transaction(function () use ($seenData, $newMessageIds, $now) {
                    // Insert new seen records
                    SeenMessage::insertOrIgnore($seenData);

                    // Update messages' updated_at
                    Message::whereIn('id', $newMessageIds)
                        ->update(['updated_at' => $now]);
                });
            }
        }

        $messageQuery = Message::where('chat_id', $chat_id)
        ->orderBy('created_at', 'desc'); // Important: order by date descending

        $totalMessages = $messageQuery->count();
        $messages = $messageQuery->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->reverse(); // Reverse to maintain chronological order

        $lastMessageIdInEntireChat = Message::where('chat_id', $chat_id)
            ->orderBy('created_at', 'desc')
            ->value('id');

        $formattedMessages = [];
        if(!empty($messages)){
            foreach($messages as $val){
                $formattedMessageTime = null;
                $messageId = $val->id;
                $messageUser = $val->user_id;
                $messageContent = $val->message;
                $messageStatus = $val->status;
                $messageLastSeen = $val->last_seen;
                $messageTime = Carbon::parse($val->created_at);
                if ($messageTime->isToday('Asia/Manila')) {
                    $formattedMessageTime = $messageTime->format('h:i A'); // e.g., "02:30 PM"
                } else {
                    $formattedMessageTime = $messageTime->format('h:i A, M d'); // e.g., "02:30 PM, Aug 10"
                }

                $reactions = Reactions::where('message_id', $messageId)->with('user')->get();
                $chatter = User::find($messageUser);

                $chatParticipant = ChatParticipant::where('chat_id', $chat_id)
                    ->where('user_id', $messageUser)
                    ->first();

                $userName = $chatParticipant && !empty($chatParticipant->nickname)
                    ? $chatParticipant->nickname
                    : ($chatter->name ?? 'Unknown');

                $formattedMessages[] = [
                    'chat_id' => $chat_id,
                    'replied_id' => $val->replied_id,
                    'task_id' => $val->task_id,
                    'message_id' => $messageId,
                    'user_id' => $messageUser,
                    'message' =>  $messageContent, // Photo of the other participant or group
                    'status' => $messageStatus, // Online status of the other participant or group
                    'last_seen' => $messageLastSeen, // Number of unseen messages
                    'created_at' => $formattedMessageTime,
                    'photo' => $chatter->photo,
                    'my_id' => Auth::id(),
                    'is_edited' => $val->is_edited,
                    'is_forwarded' => $val->is_forwarded,
                    'is_unsend' => $val->is_unsend,
                    'is_pinned' => $val->is_pinned,
                    'user_name' => $userName,
                    'reactions' => $reactions,
                    'last_message_id' =>  $lastMessageIdInEntireChat
                ];
            }
        }

        $convoAttachments = Message::with('attachments')
            ->where('chat_id', $chat_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $attachmentData = [];
        if ($convoAttachments->isNotEmpty()){
            foreach ($convoAttachments as $convo) {
                // Attachments are already loaded, no need for additional query
                foreach ($convo->attachments as $attachment) {
                    $filePath = $attachment->file_path;
                    $fileName = basename($filePath);
                    $parts = explode('_', $fileName, 2);
                    $originalNameWithExt = count($parts) === 2 ? $parts[1] : $fileName;

                    $attachmentData[] = [
                        'id' => $attachment->id,
                        'path' => $filePath,
                        'type' => $attachment->file_type,
                        'name' => $originalNameWithExt,
                        'message_id' => $convo->id,
                        'created_at' => $attachment->created_at
                    ];
                }
            }
        }

        $customNickname = ChatParticipant::where('chat_id', $chat_id)
        ->join('users', 'users.id', '=', 'chat_participants.user_id')
        ->get(['chat_participants.user_id', 'chat_participants.nickname', 'chat_participants.chat_id', 'chat_participants.is_admin', 'chat_participants.is_creator', 'users.name', 'users.photo', 'users.is_online']);
        $authId = Auth::id(); // Get the logged-in user ID

        $imAdmin = ChatParticipant::where('chat_id', $chat_id)
            ->where('user_id', $authId)
            ->where(function ($query) {
                $query->where('is_admin', 1)
                    ->orWhere('is_creator', 1);
            })
            ->exists(); // Returns true if user is an admin/creator

        // Convert boolean to integer (1 = admin, 0 = not admin)
        $imAdmin = $imAdmin ? 1 : 0;
        foreach ($customNickname as $user) {
            $user->im_admin = $imAdmin;
            $user->im_user = ($user->user_id === $authId) ? 1 : 0;
        }

        $toAddMember = User::leftJoin('chat_participants', function($join) use ($chat_id) {
                $join->on('chat_participants.user_id', '=', 'users.id')
                    ->where('chat_participants.chat_id', $chat_id);
            })
            ->whereNull('chat_participants.user_id') // Users who are NOT in the chat
            ->get(['users.*']);


        return response()->json([
            'messages' => $formattedMessages,
            'otherPart' => $otherPart,
            'hasMore' => ($page * $perPage) < $totalMessages, // Indicate if more messages exist
            'currentPage' => $page,
            'totalMessages' => $totalMessages,
            'pinnedMessages' => $pinnedMessages,
            'chat_info' => $chat,
            'convoAttachments' => $attachmentData,
            'customNickname' => $customNickname,
            'isMuted' => $chatPart,
            'convo_photo' => $convoPhoto,
            'toAddMember' => $toAddMember
        ]);
    }

    public function HeadChatSendMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'attachments' => 'nullable|array|max:20', // Limit attachments to 20
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,ico,tiff,psd,ai,eps,pdf,doc,docx,txt,ppt,pptx,xls,xlsx,odt,ods,odp,rtf,csv,zip,rar,7z|max:10240', // 10MB max per file
            'replied_id' => 'nullable'
        ]);

        if ($validator->fails()) {
            $isAttachmentError = $validator->errors()->has('attachments') ||
                        $validator->errors()->has('attachments.*');
            $errorMessages = collect($validator->errors()->all())->map(function ($error, $index) {
                return ($index + 1) . ". " . $error;
            })->implode("\n");

            $status = $isAttachmentError ? 'attachmentError' : 'error';

            return response()->json([
                'status' => $status,
                'message' => "Oops! Some fields are missing or invalid:\n" . $errorMessages
            ]);
        }

        if($request->message === null && empty($request->attachments)){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! You can't leave attachments and message empty at the same time"
            ]);
        }

        $chatId = $request->chat_id;

        if(isset($request->replied_id)){
            $message = Message::create([
                'chat_id' => $chatId,
                'replied_id' => $request->input('replied_id'),
                'user_id' => Auth::id(),
                'message' => $request->input('message'),
                'status' => 'sent',
            ]);
        } else {
            $message = Message::create([
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'message' => $request->input('message'),
                'status' => 'sent',
            ]);
        }


        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Validate file size (max 10MB)
                $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                $uniqueId = uniqid();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileName = $uniqueId . '_' . $originalName . '.' . $extension;
                $uploadPath = public_path('upload');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file first
                $file->move($uploadPath, $fileName);

                // Then get MIME type from the moved file
                $filePath = $uploadPath . '/' . $fileName;
                $mimeType = mime_content_type($filePath);
                // Save the attachment details to the database
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => 'upload/' . $fileName,
                    'file_type' => $mimeType,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function reloadChatMessage(Request $request) {
        $userId = Auth::id();
        $checkIfStill = ChatParticipant::where('user_id', $userId)->where('chat_id', $request->chat_display_id)->first();
        if(!$checkIfStill){
            return response()->json([
                'status' => 'nothere',
                'chat_id' => $checkIfStill->chat_id,
            ]);
        }
        $chatParticipants = ChatParticipant::where('user_id', $userId)
                                        ->where('chat_id', $request->chat_display_id)
                                        ->get();

        if ($chatParticipants->isEmpty()) {
            return response()->json([
                'status' => 'no_changes',
                'chatUpdate' => $request->chatUpdate ?? [],
                'messages' => []
            ]);
        }


        $responseData = [];
        $clientSnapshot = json_decode($request->chatUpdate, true) ?? [];

        foreach ($chatParticipants as $participant) {
            $chatId = $participant->chat_id;

            $currentUserId = Auth::id();

            // Retrieve message IDs that need to be marked as seen
            $messageIds = Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $currentUserId)
                ->whereIn('status', ['sent', 'delivered', 'seen'])
                ->pluck('id');

            if ($messageIds->isNotEmpty()) {
                // Update status for relevant messages
                Message::whereIn('id', $messageIds)
                    ->whereIn('status', ['sent', 'delivered'])
                    ->update(['status' => 'seen']);

                // Get existing seen records
                $existingSeen = SeenMessage::where('user_id', $currentUserId)
                    ->whereIn('message_id', $messageIds)
                    ->pluck('message_id')
                    ->toArray();

                // Find new message IDs that need to be marked as seen
                $newMessageIds = array_diff($messageIds->toArray(), $existingSeen);

                if (!empty($newMessageIds)) {
                    $now = Carbon::now();

                    // Bulk insert with timestamps
                    $seenData = array_map(function ($messageId) use ($currentUserId, $now) {
                        return [
                            'message_id' => $messageId,
                            'user_id' => $currentUserId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $newMessageIds);

                    // Use transaction for atomic operations
                    DB::transaction(function () use ($seenData, $newMessageIds, $now) {
                        // Insert new seen records
                        SeenMessage::insertOrIgnore($seenData);

                        // Update messages' updated_at
                        Message::whereIn('id', $newMessageIds)
                            ->update(['updated_at' => $now]);
                    });
                }
            }

            $messages = Message::with('user')
                            ->where('chat_id', $chatId)
                            ->orderBy('created_at', 'asc')
                            ->get();

            $lastMessageId = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->value('id');

            $messagesResult = $messages->map(function ($message) use ($userId, $lastMessageId) {
                $formattedTime = Carbon::parse($message->created_at);
                $formattedTime = $formattedTime->isToday()
                    ? $formattedTime->format('h:i A')
                    : $formattedTime->format('h:i A, M d');

                $reactions = Reactions::where('message_id', $message->id)->with('user')->get();

                $chatParticipant = ChatParticipant::where('chat_id', $message->chat_id)
                    ->where('user_id', $message->user_id)
                    ->first();

                $userName = $chatParticipant && !empty($chatParticipant->nickname)
                    ? $chatParticipant->nickname
                    : ($message->user->name ?? 'Unknown');
                return [
                    'message_id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'replied_id' => $message->replied_id,
                    'task_id' => $message->task_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'status' => $message->status,
                    'last_seen' => $message->last_seen,
                    'created_at' => $formattedTime,
                    'photo' => $message->user->photo,
                    'my_id' => $userId,
                    'is_edited' => $message->is_edited,
                    'is_forwarded' => $message->is_forwarded,
                    'updated_at' => $message->updated_at->toIso8601String(),
                    'is_unsend' => $message->is_unsend,
                    'is_pinned' => $message->is_pinned,
                    'user_name' => $userName,
                    'reactions' => $reactions,
                    'last_message_id' => $lastMessageId
                ];
            })->toArray();

            $currentSnapshot = [
                'chat_id' => $chatId,
                'messages' => array_column($messagesResult, null, 'message_id')
            ];

            $clientChatData = collect($clientSnapshot)
                            ->where('chat_id', $chatId)
                            ->first();

            $chatClientSnapshot = $clientChatData['messages'] ?? [];

            $currentMessageIds = array_keys($currentSnapshot['messages']);
            $clientMessageIds = array_keys($chatClientSnapshot);

            $newMessageIds = array_diff($currentMessageIds, $clientMessageIds);
            $deletedMessageIds = array_diff($clientMessageIds, $currentMessageIds);

            $updatedMessages = [];
            foreach ($currentSnapshot['messages'] as $messageId => $serverMessage) {
                if (isset($chatClientSnapshot[$messageId])) {
                    $clientMessage = $chatClientSnapshot[$messageId];

                    $hasChange = false;
                    $fieldsToCompare = ['message', 'status', 'is_edited', 'is_forwarded'];

                    // Check field differences (handle missing client fields)
                    foreach ($fieldsToCompare as $field) {
                        $serverValue = $serverMessage[$field] ?? null;
                        $clientValue = $clientMessage[$field] ?? null; // Gracefully handle missing keys

                        if ($serverValue != $clientValue) {
                            $hasChange = true;
                            break;
                        }
                    }

                    // Check updated_at if no other changes
                    if (!$hasChange && isset($serverMessage['updated_at'], $clientMessage['updated_at'])) {
                        $serverTime = Carbon::parse($serverMessage['updated_at']);
                        $clientTime = Carbon::parse($clientMessage['updated_at']);
                        $hasChange = !$serverTime->equalTo($clientTime);
                    }

                    if ($hasChange) {
                        $updatedMessages[] = $serverMessage;
                    }
                }
            }

            $status = 'no_changes';
            if (empty($chatClientSnapshot)) {
                $status = 'initial_load';
            } else {
                $hasCountChange = !empty($newMessageIds) || !empty($deletedMessageIds);
                $hasUpdates = !empty($updatedMessages);

                if ($hasCountChange) {
                    $status = 'count_changed';
                } elseif ($hasUpdates) {
                    $status = 'chat_updated';
                }
            }

            $response = [
                'status' => $status,
                'chatUpdate' => $currentSnapshot
            ];

            if (!empty($newMessageIds)) {
                $response['newMessages'] = array_values(array_intersect_key(
                    $currentSnapshot['messages'],
                    array_flip($newMessageIds)
                ));
            }
            if (!empty($deletedMessageIds)) {
                $response['deletedMessageIds'] = $deletedMessageIds;
            }
            if (!empty($updatedMessages)) {
                $response['updatedMessages'] = $updatedMessages;
            }

            $responseData[$chatId] = $response;
        }

        return response()->json($responseData);
    }

    public function HeadChatCheckWhoSeen(Request $request){
        $messageId = $request->message;
        $messageseen = SeenMessage::where('message_id', $messageId)->with('user')->get();

        if (!$messageseen->isNotEmpty()) {
            return response()->json([
                'status' => 'error'
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'seen' => $messageseen,
            ]);
        }
    }

    public function HeadCheckMessageAttachment(Request $request){
        $messageId = $request->message;
        $messageExists = Message::where('id', $messageId)->exists();

        if (!$messageExists) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $attachments = MessageAttachment::where('message_id', $messageId)->get();
            $chat = Message::find($messageId);
            if ($attachments->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no attachment in this chat. id:".$messageId
                ]);
            }

            $attachmentData = [];
            foreach ($attachments as $attachment) {
                $filePath = $attachment->file_path;
                $fileName = basename($filePath);
                $parts = explode('_', $fileName, 2);
                $originalNameWithExt = count($parts) === 2 ? $parts[1] : $fileName;

                $attachmentData[] = [
                    'chat_id' => $chat->chat_id,
                    'id' => $attachment->id,
                    'path' => $filePath,
                    'type' => $attachment->file_type,
                    'name' => $originalNameWithExt
                ];
            }

            return response()->json([
                'status' => 'success',
                'attachments' => $attachmentData
            ]);
        }
    }

    public function HeadChatRepliedUser(Request $request){
        $messageId = $request->message;
        $messageExists = Message::where('id', $messageId)->first();
        if (!$messageExists) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $user = User::where('id', $messageExists->user_id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no user in this chat. id:".$messageId
                ]);
            }

            return response()->json([
                'status' => 'success',
                'user' => $user
            ]);
        }
    }

    public function HeadcheckMessageReply(Request $request){
        $messageId = $request->message;
        $message = Message::where('id', $messageId)->first();

        if (!$message) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $repliedMessage = Message::find($message->replied_id);

            if (!$repliedMessage) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no message in this chat. id:".$messageId
                ]);
            }

            $fromuser = User::find($message->user_id);
            if (!$fromuser) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no user in this chat. id:".$messageId
                ]);
            }

            $user = User::find($repliedMessage->user_id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no user in this chat. id:".$messageId
                ]);
            }

            return response()->json([
                'status' => 'success',
                'reply' => $repliedMessage,
                'user' => $user,
                'fromuser' => $fromuser
            ]);
        }
    }

    public function HeadChatGetTaskInfo(Request $request){
        $task_id = $request->task;

        $task = Task::find($task_id);

        $userIds = [];

        if($task){
            if($task->type == 'Solo'){
                $solo = Task_solo::where('task_id', $task->id)->first();
                if ($solo) {
                    $userIds[] = $solo->user_id; // Just store the ID directly
                }
            } else if($task->type == 'Group'){
                $groups = Task_group::where('task_id', $task->id)->get();
                foreach ($groups as $group) {
                    $userIds[] = $group->user_id; // Just store the ID directly
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'task' => $task,
            'userIds' => $userIds
        ]);
    }

    public function HeadGetEditMessage(Request $request){
        $messageId = $request->message;
        $message = Message::where('id', $messageId)->first();

        if (!$message) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        }
    }

    public function HeadChatEditMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            $errorMessages = collect($validator->errors()->all())->map(function ($error, $index) {
                return ($index + 1) . ". " . $error;
            })->implode("\n");

            return response()->json([
                'status' => 'error',
                'message' => "Oops! Some fields are missing or invalid:\n" . $errorMessages
            ]);
        }

        $messageId = $request->message_id;

        $message = Message::find($messageId);

        if ($message) {
            EditMessage::create([
                'chat_id' => $message->chat_id,
                'message_id' => $message->id,
                'user_id' => $message->user_id,
                'message' => $message->message
            ]);

            $message->update([
                'message' => $request->input('message'),
                'is_edited' => 1,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Message updated successfully']);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message might not be existing anymore please try again"
            ]);
        }
    }

    public function HeadViewEditMessage(Request $request){
        $messageId = $request->message;
        $message = EditMessage::where('message_id', $messageId)->get();

        if ($message->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {

            foreach ($message as $val) {
                $messageTime = Carbon::parse($val->created_at);
                if ($messageTime->isToday('Asia/Manila')) {
                    $val->time = $messageTime->format('h:i A'); // e.g., "02:30 PM"
                } else {
                    $val->time = $messageTime->format('h:i A, M d'); // e.g., "02:30 PM, Aug 10"
                }
            }
            return response()->json([
                'status' => 'success',
                'edited' => $message,
            ]);
        }
    }

    public function HeadViewMessageContact(Request $request){
        $userId = Auth::id();
        $chats = ChatParticipant::where('chat_participants.user_id', $userId)
        ->where('chat_id', '!=', $request->chat)
        ->join('chats', 'chats.id', '=', 'chat_participants.chat_id')
        ->select('chat_participants.chat_id', 'chats.type', 'chats.name')
        ->get();

        // Process each chat
        $chatResult = [];
        foreach ($chats as $chat) {
            $chatId = $chat->chat_id;
            $chatType = $chat->type;
            $chatName = $chat->name;

            if ($chatType === 'user_to_user') {
                // For user-to-user chats, get the other participant's name, photo, and online status
                $otherParticipant = ChatParticipant::where('chat_id', $chatId)
                    ->where('user_id', '!=', $userId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->first(['users.name', 'users.photo', 'users.is_online']);
                $chatName = $otherParticipant->name ?? 'Unknown User';
                $chatPhoto = $otherParticipant->photo; // Default photo if none
                $isOnline = $otherParticipant->is_online ?? false; // Default to false if none
            } elseif ($chatType === 'group' && empty($chatName)) {
                // For group chats with no name, get all participants' names, photos, and online status
                $participants = ChatParticipant::where('chat_id', $chatId)
                    ->join('users', 'users.id', '=', 'chat_participants.user_id')
                    ->get(['users.name', 'users.photo', 'users.is_online']);

                $participantNames = $participants->pluck('name')->toArray();
                $chatName = $participantPhotos;

                // Get the first participant's photo as the group chat photo (or use a default)
                $participantPhotos = $participants->take(2)->pluck('photo')->toArray();
                $chatPhoto = $participantPhotos;

                // Check if any participant is online
                $isOnline = $participants->contains('is_online', true);
            }


            // Add the chat to the result
            $chatResult[] = [
                'chat_id' => $chatId,
                'type' => $chatType,
                'name' => $chatName,
                'photo' => $chatPhoto,
                'is_online' => $isOnline,
            ];
        }

        return response()->json([
            'status' => 'success',
            'chatResult' => $chatResult,
        ]);
    }

    public function HeadChatSendForwardMessage(Request $request){
        $messageId = $request->message;
        $chatId = $request->chat;

        $userId = Auth::id();

        $getMessage = Message::find($messageId);

        if(!$getMessage){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! You can't forward a not existing message"
            ]);
        }
        // Create the message
        $message = Message::create([
            'chat_id' => $chatId,
            'user_id' => Auth::id(),
            'message' => $getMessage->message,
            'is_forwarded' => 1,
            'status' => 'sent',
        ]);

        // Handle attachments
        $originalAttachments = MessageAttachment::where('message_id', $messageId)->get();

        if ($originalAttachments->isNotEmpty()) {
            foreach ($originalAttachments as $originalAttachment) {
                // Get original file path
                $originalFilePath = public_path($originalAttachment->file_path);

                // Verify file exists
                if (!file_exists($originalFilePath)) {
                    continue; // Skip if file doesn't exist
                }

                // Generate new unique filename
                $fileInfo = pathinfo($originalAttachment->file_path);
                $uniqueId = uniqid();
                $newFileName = $uniqueId . '_' . $fileInfo['filename'] . '.' . $fileInfo['extension'];
                $newFilePath = 'upload/' . $newFileName;

                // Copy the file
                copy($originalFilePath, public_path($newFilePath));

                // Create new attachment record
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => $newFilePath,
                    'file_type' => $originalAttachment->file_type,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function HeadCheckWhoForward(Request $request){
        $messageId = $request->message;
        $message = Message::where('id', $messageId)->first();

        if (!$message) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $user = User::find($message->user_id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! There's no user in this chat. id:".$messageId
                ]);
            }

            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        }
    }

    public function HeadChatUnsendMessage(Request $request){
        DB::transaction(function () use ($request) {
            $message = Message::with('attachments')
                ->where('id', $request->message)
                ->whereNotIn('is_unsend', [1, 2])
                ->firstOrFail();

            // Authorization check - ensure user owns the message
            if ($message->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action');
            }

            $message->update(['is_unsend' => intval($request->type)]);

            if ($request->type == 2) {
                // Delete related data
                PinnedMessage::where('message_id', $message->id)->delete();

                // Delete attachments and files
                foreach ($message->attachments as $media) {
                    $filePath = public_path($media->file_path);

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                // Bulk delete attachments
                MessageAttachment::where('message_id', $message->id)->delete();
            }
        });

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function HeadChatPinMessage(Request $request){
        $messageId = $request->message;
        $chatId = $request->chat;
        $message = Message::where('id', $messageId)->whereNotIn('is_unsend', [1, 2])->first();

        if (!$message) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $message->update([
                'is_pinned' => 1,
            ]);

            Message::create([
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'message' => 'pinned a message.',
                'status' => 'announcement',
            ]);

            PinnedMessage::create([
                'chat_id' => $chatId,
                'message_id' => $message->id,
                'user_id' => $message->user_id,
                'pinnedby_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => 'success'
            ]);
        }
    }

    public function HeadChatUnpinMessage(Request $request){
        $messageId = $request->message;
        $chatId = $request->chat;
        $message = Message::where('id', $messageId)->whereNotIn('is_unsend', [1, 2])->first();

        if (!$message) {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        } else {
            $message->update([
                'is_pinned' => 0,
            ]);

            Message::create([
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'message' => 'unpinned a message.',
                'status' => 'announcement',
            ]);

            PinnedMessage::where('message_id', $message->id)->delete();

            return response()->json([
                'status' => 'success'
            ]);
        }
    }

    public function HeadChatReact(Request $request){
        $message_id = $request->message_id;
        $reaction = $request->reaction;
        $user_id = Auth::id();

        $message = Message::find($message_id);
        if(!$message){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This message does not exist. It might have been deleted. Please check again."
            ]);
        }

        $check = Reactions::where('message_id', $message_id)->where('user_id', $user_id)->first();

        if($check){
            $check->reaction === $reaction ? $check->delete() : $check->update(['reaction' => $reaction]);
        } else {
            Reactions::create([
                'message_id' => $message_id,
                'user_id' => $user_id,
                'reaction' => $reaction
            ]);
        }

        $message->touch();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function reloadChatPinned(Request $request) {
        $chat_id = $request->chat_id;
        $user = Auth::user();

        // Fetch the latest pinned message
        $pinnedMessages = [];
        $latestPin = PinnedMessage::where('chat_id', $chat_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestPin) {
            $message = Message::where('id', $latestPin->message_id)
                ->where('is_unsend', 0)
                ->first();

            if ($message) {
                $pinnedUser = User::find($message->user_id);
                $pinnedTime = Carbon::parse($latestPin->created_at)
                    ->setTimezone('Asia/Manila');

                $formattedTime = $pinnedTime->isToday()
                    ? $pinnedTime->format('h:i A')
                    : $pinnedTime->format('h:i A, M d');

                $chatParticipant = ChatParticipant::where('chat_id', $chat_id)
                    ->where('user_id', $message->user_id)
                    ->first();

                $userName = $chatParticipant && !empty($chatParticipant->nickname)
                    ? $chatParticipant->nickname
                    : ($pinnedUser->name ?? 'Unknown');

                $pinnedMessages[] = [
                    'chat_id' => $chat_id,
                    'message_id' => $message->id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'created_at' => $formattedTime,
                    'photo' => $pinnedUser->photo ?? null,
                    'user_name' => $userName,
                    'updated_at' => $latestPin->updated_at->toDateTimeString()
                ];
            }
        }

        // Build snapshot
        $currentSnapshot = $latestPin
            ? [$latestPin->message_id => $latestPin->updated_at->toDateTimeString()]
            : [];

        $clientSnapshot = json_decode($request->pinnedUpdate, true) ?: [];

        // Always return current pinned state
        return response()->json([
            'status' => 'success',
            'pinnedUpdate' => $currentSnapshot,
            'pinned' => $pinnedMessages
        ]);
    }

    public function HeadViewPinnedMessage(Request $request){
        $chat_id = $request->chat;

        // Fetch the latest pinned message
        $pinnedMessages = [];
        $pinned = PinnedMessage::where('chat_id', $chat_id)
            ->orderBy('id', 'desc')
            ->get();

        if ($pinned->isNotEmpty()) {
            foreach($pinned as $pin){
                $message = Message::where('id', $pin->message_id)
                ->where('is_unsend', 0)
                ->first();

                if ($message) {
                    $pinnedUser = User::find($message->user_id);
                    $pinnedTime = Carbon::parse($pin->created_at)
                        ->setTimezone('Asia/Manila');

                    $formattedTime = $pinnedTime->isToday()
                        ? $pinnedTime->format('h:i A')
                        : $pinnedTime->format('h:i A, M d');

                    $byUser = User::find($pin->pinnedby_id);
                    $pinnedChatParticipant = ChatParticipant::where('chat_id', $chat_id)
                        ->where('user_id', $message->user_id)
                        ->first();

                    $userName = $pinnedChatParticipant && !empty($pinnedChatParticipant->nickname)
                        ? $pinnedChatParticipant->nickname
                        : ($pinnedUser->name ?? 'Unknown');

                    // Check if the pinned-by user has a nickname in ChatParticipant
                    $byChatParticipant = ChatParticipant::where('chat_id', $chat_id)
                        ->where('user_id', $pin->pinnedby_id)
                        ->first();

                    $byName = $byChatParticipant && !empty($byChatParticipant->nickname)
                        ? $byChatParticipant->nickname
                        : ($byUser->name ?? 'Unknown');

                    $pinnedMessages[] = [
                        'chat_id' => $chat_id,
                        'message_id' => $message->id,
                        'user_id' => $message->user_id,
                        'message' => $message->message,
                        'created_at' => $formattedTime,
                        'photo' => $pinnedUser->photo ?? null,
                        'user_name' => $userName,
                        'by_name' => $byName
                    ];
                }
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "Oops! There's no existing pin message yet, try to pin one message first."
            ]);
        }

        return response()->json([
            'status' => 'success',
            'pinned' => $pinnedMessages
        ]);
    }

    public function HeadChatSetNickname(Request $request){
        $user_id = $request->user_id;
        $chat_id = $request->chat_id;
        $nickname = $request->nickname;
        $user = User::find($user_id);
        $userPart = ChatParticipant::where('user_id', $user_id)->where('chat_id', $chat_id)->first();
        if (!$userPart) {
            return response()->json([
                'status' => 'error',
                'message' => "User is not a participant in this chat."
            ]);
        }

        if ($nickname === null || $nickname === "") {
            if ($userPart->nickname !== null) {
                $userPart->update(['nickname' => null]); // Set to null

                Message::create([
                    'chat_id' => $chat_id,
                    'user_id' => Auth::id(),
                    'message' => 'cleared "' . (Auth::id() == $user_id ? 'Own' : $user->name) . '" nickname.',
                    'status' => 'nickname',
                ]);

                return response()->json(['status' => 'success']);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => "Oops! You can't set an empty nickname when it's already empty."
                ]);
            }
        }

        if (strlen(trim($nickname)) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Nickname cannot be only spaces."
            ]);
        }

        if ($userPart->nickname === $nickname) {
            return response()->json([
                'status' => 'error',
                'message' => "This nickname is already set."
            ]);
        }

        $userPart->update([
            'nickname' => $nickname
        ]);

        Message::create([
            'chat_id' => $chat_id,
            'user_id' => Auth::id(),
            'message' => 'set "' . (Auth::id() == $user_id ? 'Own' : $user->name) . '" nickname as "'.$nickname.'".',
            'status' => 'nickname',
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadChatSetMutedChat(Request $request){
        $chat_id = $request->chat_id;
        $muted = (int) $request->muted;
        $isMuted = $muted == 0 ? 1 : 0;

        $userPart = ChatParticipant::where('user_id', Auth::id())->where('chat_id', $chat_id)->first();
        if (!$userPart) {
            return response()->json([
                'status' => 'error',
                'message' => "User is not a participant in this chat."
            ]);
        }

        $userPart->update([
            'is_muted' => $isMuted
        ]);

        return response()->json([
            'status' => 'success',
            'message' => ($isMuted == 1 ? 'Successfully Muted' : 'Successfully Unmuted')
        ]);
    }

    public function HeadChatSearchMessageValue(Request $request) {
        $message_val = trim($request->message);
        $chat_id = $request->chat;
        $page = $request->page ?? 1;

        if (empty($message_val)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter some value before searching.'
            ]);
        }

        $keywords = array_filter(explode(' ', $message_val), fn($word) => !empty($word));

        $query = Message::with(['user' => function($query) {
                $query->select('id', 'username', 'name', 'photo');
            }])
            ->leftJoin('chat_participants', function($join) use ($chat_id) {
                $join->on('messages.user_id', '=', 'chat_participants.user_id')
                    ->where('chat_participants.chat_id', '=', $chat_id);
            })
            ->select('messages.*', 'chat_participants.nickname as participant_nickname')
            ->where('messages.chat_id', $chat_id)
            ->whereNotIn('is_unsend', [1, 2])
            ->whereNotIn('status', ['announcement', 'nickname'])
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('messages.message', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->orderBy('messages.created_at', 'desc');

        $messages = $query->paginate(20, ['*'], 'page', $page);  // Use regular paginate

        return response()->json([
            'status' => 'success',
            'messages' => $messages->map(function($message) use ($chat_id) {
                // Get display name with fallback logic
                $displayName = $message->participant_nickname
                    ?? $message->user->name
                    ?? 'Unknown User';

                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'chat_id' => $chat_id,
                    'is_pinned' => $message->is_pinned,
                    'user' => [
                        'id' => $message->user->id ?? null,
                        'name' => $displayName,
                        'username' => $message->user->username ?? 'unknown',
                        'avatar' => $message->user->photo ?? null
                    ]
                ];
            }),
            'current_page' => $messages->currentPage(),
            'next_page' => $messages->hasMorePages() ? $messages->currentPage() + 1 : null,
        ]);
    }

    public function getChatMedia(Chat $chat){
        $media = Message::with(['attachments', 'user'])
            ->where('chat_id', $chat->id)
            ->get()
            ->flatMap(function($message) {
                return $message->attachments->map(function($attachment) use ($message) {
                    return [
                        'id' => $attachment->id,
                        'url' => asset($attachment->file_path),
                        'type' => Str::before($attachment->file_type, '/'),
                        'timestamp' => $attachment->created_at->format('M d, Y H:i'),
                        'sender' => $message->user ? [
                            'id' => $message->user->id,
                            'name' => $message->user->name,
                            'avatar' => $message->user->photo ?
                                asset('upload/photo_bank/'.$message->user->photo) :
                                asset('upload/nophoto.jfif')
                        ] : null
                    ];
                });
            })
            ->filter(fn($m) => $m['type'] === 'image')
            ->values() // Reset array keys
            ->toArray(); // Convert to plain array

        return response()->json([
            'status' => 'success',
            'media' => $media
        ]);
    }

    public function HeadChatSaveMeeting(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Validation failed: " . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Generate Jitsi-compatible room name
            $roomName = 'jitsi-' . Str::slug($request->name) . '-' . uniqid();
            $roomUrl = "https://meet.jit.si/" . $roomName;

            // Create meeting record
            Meeting::create([
                'room_id' => $roomName,
                'room_url' => $roomUrl,
                'room_name' => $request->name,
                'description' => $request->description,
                'started_at' => now(),
                'user_id' => Auth::id(),
                'is_active' => true
            ]);

            $dept = Member::with('user')->get();
            foreach($dept as $member){
                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Admin: ' . Auth::user()->name . ' ended the meeting "' . $request->name . '" To Emergency';
                $notif->type = 'info';
                $notif->save();
            }

            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "Ended a meeting. User: ".Auth::user()->name." ID: ".Auth::user()->id;
            $log->description = date('Y-m-d');
            $log->save();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            \Log::error('Meeting Creation Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // No changes needed to AdminChatRemoveMeeting

    public function HeadChatRemoveMeeting(Request $request){
        $room_id = $request->room;
        if(!isset($request->delete)){
            $updated = Meeting::where('room_id', $room_id)->update([
                'is_active' => 0
            ]);

            if ($updated) {
                return response()->json([
                    'status' => 'success'
                ]);
            }

            $dept = Member::with('user')->get();
            foreach($dept as $member){
                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Admin: ' . Auth::user()->name . ' ended the meeting "' . $updated->room_name . '" To Emergency';
                $notif->type = 'info';
                $notif->save();
            }

            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "Ended a meeting. User: ".Auth::user()->name." ID: ".Auth::user()->id;
            $log->description = date('Y-m-d');
            $log->save();

            return response()->json([
                'status' => 'error',
                'message' => 'Meeting room not found'
            ]);
        } else {
            $updated = Meeting::where('room_id', $room_id)->delete();
            if ($updated) {
                return response()->json([
                    'status' => 'success'
                ]);
            }

            $log = new Log();
            $log->name = Auth::user()->name;
            $log->action = "Delete a history meeting. User: ".Auth::user()->name." ID: ".Auth::user()->id;
            $log->description = date('Y-m-d');

            return response()->json([
                'status' => 'error',
                'message' => 'Meeting room not found'
            ]);
        }
    }

    public function HeadChatCreateGroupMessage(Request $request){
        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids) >= 2) {
            $user_ids = $request->user_ids;

            $chat = Chat::create([
                'type' => 'group'
            ]);

            $chat_id = $chat->id;

            foreach ($user_ids as $id) {
                ChatParticipant::create([
                    'chat_id' => $chat_id,
                    'user_id' => $id,
                ]);
            }

            ChatParticipant::create([
                'chat_id' => $chat_id,
                'user_id' => Auth::id(),
                'is_admin' => 1,
                'is_creator' => 1
            ]);

            Message::create([
                'chat_id' => $chat_id,
                'user_id' => Auth::id(),
                'message' => 'create group.',
                'status' => 'nickname',
            ]);

            foreach($user_ids as $id){
                $notif = new Notification();
                $notif->user_id = $id;
                $notif->message = Auth::user()->name." added you to the group";
                $notif->type = 'info';
                $notif->save();
            }

            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'At least 2 users must be selected to create a group.'
            ]);
        }
    }

    public function HeadChatSetConvoImage(Request $request){
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            // Validate file type, size, and image integrity
            $extension = strtolower($file->getClientOriginalExtension());
            $request->validate([
                'photo' => 'required|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:10240'
            ]);

            // Get file extension and verify it's allowed

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid image type. Allowed: JPG, JPEG, PNG, GIF, BMP, WEBP.'
                ]);
            }

            // Generate unique file name
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = public_path('upload');

            // Ensure upload directory exists
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file to upload directory
            $file->move($uploadPath, $fileName);

            // Save image path to database
            Chat::where('id', $request->chat_id)->update([
                'photo' => 'upload/' . $fileName,
            ]);

            Message::create([
                'chat_id' => $request->chat_id,
                'user_id' => Auth::id(),
                'message' => 'set conversation photo.',
                'status' => 'nickname',
            ]);

            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Please choose an image file before submitting.'
        ]);
    }

    public function HeadChatUnsetConvoImage(Request $request){
        $chat_id = $request->chat;

        // Find the chat record
        $chat = Chat::find($chat_id);

        if ($chat && $chat->photo) {
            // Get the file path
            $photoPath = public_path($chat->photo);

            // Delete the file if it exists
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }

            // Remove the photo from the database
            $chat->update(['photo' => null]);

            // Create an announcement message
            Message::create([
                'chat_id' => $chat_id,
                'user_id' => Auth::id(),
                'message' => 'unset conversation photo.',
                'status' => 'nickname',
            ]);

            return response()->json([
                'status' => 'success'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No conversation photo found to remove.'
        ]);
    }

    public function HeadChatSetConvoName(Request $request){
        if (trim($request->name) !== '') {
            $name = preg_replace('/\s+/', ' ', trim($request->name)); // Remove spaces from beginning and end

            if (strlen($name) > 255) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can\'t enter a name longer than 255 characters.'
                ]);
            }

            // Save name to database
            Chat::where('id', $request->chat_id)->update([
                'name' => $name,
            ]);

            Message::create([
                'chat_id' => $request->chat_id,
                'user_id' => Auth::id(),
                'message' => 'Set conversation name to "' . $name . '".',
                'status' => 'nickname',
            ]);

            return response()->json([
                'status' => 'success',
            ]);
        }

        // If only spaces or empty input
        return response()->json([
            'status' => 'error',
            'message' => 'Please enter a valid name before submitting.'
        ]);

    }

    public function HeadChatUnsetConvoName(Request $request){
        $chat = Chat::where('id', $request->chat)->first();

        if ($chat && !empty($chat->name)) {
            $chat->update(['name' => null]);

            // Create an announcement message
            Message::create([
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'message' => 'unset conversation name.',
                'status' => 'nickname',
            ]);

            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No conversation name to remove or chat not found.'
        ]);
    }

    public function HeadChatAddNewMemberGroup(Request $request) {
        $chat_id = $request->chat;
        $user_id = $request->user; // Fix: Assign the correct user ID

        $chat = Chat::find($chat_id);
        if (!$chat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat is not existing anymore, please try again.'
            ]);
        }

        // Check if user is already in the chat
        $existing = ChatParticipant::where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->exists();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already in the chat.'
            ]);
        }

        // Add user to the chat
        $part = ChatParticipant::create([
            'chat_id' => $chat_id,
            'user_id' => $user_id,
        ]);

        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'This user is not existing anymore.'
            ]);
        }

        // Add system message
        Message::create([
            'chat_id' => $chat_id,
            'user_id' => Auth::id(),
            'message' => 'added ' . $user->name . ' to the group.',
            'status' => 'nickname',
        ]);

        $notif = new Notification();
        $notif->user_id = $user->id;
        $notif->message = Auth::user()->name." added you to the group";
        $notif->type = 'info';
        $notif->save();

        // Get added user details
        $addedUser = ChatParticipant::where('user_id', $user_id)
            ->join('users', 'users.id', '=', 'chat_participants.user_id')
            ->first([
                'chat_participants.user_id',
                'chat_participants.nickname',
                'chat_participants.chat_id',
                'chat_participants.is_admin',
                'chat_participants.is_creator',
                'users.name',
                'users.photo',
                'users.is_online'
            ]);

        $authId = Auth::id(); // Get the logged-in user ID

        // Check if the logged-in user is an admin/creator
        $imAdmin = ChatParticipant::where('chat_id', $chat_id)
            ->where('user_id', $authId)
            ->where(function ($query) {
                $query->where('is_admin', 1)
                    ->orWhere('is_creator', 1);
            })
            ->exists(); // Returns true if user is an admin/creator

        // Convert boolean to integer (1 = admin, 0 = not admin)
        $imAdmin = $imAdmin ? 1 : 0;

        $addedUser->im_admin = $imAdmin;
        $addedUser->im_user = ($addedUser->user_id === $authId) ? 1 : 0;

        return response()->json([
            'status' => 'success',
            'users' => $addedUser
        ]);
    }

    public function HeadChatToggleAsAdmin(Request $request){
        $chat = $request->chat;
        $user = $request->user;
        $type = $request->type;

        if($type == 'add'){
            ChatParticipant::where('chat_id', $chat)->where('user_id', $user)->update([
                'is_admin' => 1,
            ]);

            $user = User::find($user);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This user is not existing anymore.'
                ]);
            }

            Message::create([
                'chat_id' => $chat,
                'user_id' => Auth::id(),
                'message' => 'added ' . $user->name . ' as admin.',
                'status' => 'nickname',
            ]);

            $notif = new Notification();
            $notif->user_id = $user->id;
            $notif->message = Auth::user()->name." make you as admin";
            $notif->type = 'info';
            $notif->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully added as admin.'
            ]);
        } else if($type == 'remove'){
            ChatParticipant::where('chat_id', $chat)->where('user_id', $user)->update([
                'is_admin' => 0,
            ]);

            $user = User::find($user);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This user is not existing anymore.'
                ]);
            }

            Message::create([
                'chat_id' => $chat,
                'user_id' => Auth::id(),
                'message' => 'removed ' . $user->name . ' as admin.',
                'status' => 'nickname',
            ]);

            $notif = new Notification();
            $notif->user_id = $user->id;
            $notif->message = Auth::user()->name." remove you as admin";
            $notif->type = 'info';
            $notif->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully removed as admin.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Something\'s wrong.'
        ]);
    }

    public function HeadChatRemoveFromGroup(Request $request){
        $user_id = $request->user;
        $chat_id = $request->chat;

        $chat = Chat::find($chat_id);
        if (!$chat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat is not existing anymore, please try again.'
            ]);
        }

        // Check if user is already in the chat
        $participant = ChatParticipant::where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();

        if (!$participant) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already removed from the conversation.'
            ]);
        }
        $participant->delete();

        $users = User::find($user_id);

        Message::create([
            'chat_id' => $chat_id,
            'user_id' => Auth::id(),
            'message' => 'removed ' . $users->name . ' from the group.',
            'status' => 'nickname',
        ]);

        $notif = new Notification();
        $notif->user_id = $user_id;
        $notif->message = "You been removed in group chat by ".Auth::user()->name;
        $notif->type = 'info';
        $notif->save();

        return response()->json([
            'status' => 'success',
            'users' => $users
        ]);
    }

    public function HeadChatLeaveConversation(Request $request){
        DB::transaction(function () use ($request) {
            $chat_id = $request->chat;
            $user_id = Auth::id();

            $chat = Chat::findOrFail($chat_id);
            $activeAdmins = ChatParticipant::where('chat_id', $chat_id)
                ->where('is_admin', 1)
                ->where('is_creator', 0) // Optional: exclude creator from admin count
                ->count();

            // 2. Get current creator status
            $hasCreator = ChatParticipant::where('chat_id', $chat_id)
                ->where('is_creator', 1)
                ->exists();

            // 3. If no admins remain (and no creator exists)
            if ($activeAdmins === 0 && !$hasCreator) {
                $newAdmin = ChatParticipant::where('chat_id', $chat_id)
                    ->where('is_admin', 0)
                    ->when($hasCreator, function ($query) {
                        return $query->where('is_creator', 0); // Skip creator if exists
                    })
                    ->oldest('created_at') // Priority: first-joined member
                    ->first();

                if ($newAdmin) {
                    $newAdmin->update(['is_admin' => 1]);

                    // Optional: Notify new admin
                    event(new NewAdminPromoted($newAdmin->user_id, $chat_id));
                } else {
                    // Last participant left - delete chat or keep creatorless
                    Chat::find($chat_id)?->delete();
                }
            }
            $participant = ChatParticipant::where('chat_id', $chat_id)
                            ->where('user_id', $user_id)
                            ->firstOrFail();

            // Delete participant first
            $participant->delete();

            // Check remaining participants BEFORE creating message
            $remainingParticipants = ChatParticipant::where('chat_id', $chat_id)->count();

            if ($remainingParticipants > 0) {
                // Only create message if chat will persist
                Message::create([
                    'chat_id' => $chat_id,
                    'user_id' => $user_id,
                    'message' => 'left the group',
                    'status' => 'nickname' // More appropriate type
                ]);
            }

            // Cleanup only if no participants remain
            if ($remainingParticipants === 0) {
                // Efficient attachment deletion
                $messages = Message::with('attachments')->where('chat_id', $chat_id)->get();

                foreach ($messages as $message) {
                    SeenMessage::where('message_id', $message->id)->delete();
                    foreach ($message->attachments as $media) {
                        $file = public_path($media->file_path);
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                    // Bulk delete attachments
                    MessageAttachment::where('message_id', $message->id)->delete();
                }

                // Bulk delete messages
                Message::where('chat_id', $chat_id)->delete();
                $chat->delete();
            }
        });

        return response()->json(['status' => 'success']);
    }

    public function HeadChatUnsetChatIsHere(){
        $user_id = Auth::id();
        ChatParticipant::where('user_id', $user_id)->where('is_here', 1)->update([
            'is_here' => 0
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadChatSetChatIsHere(){
        $user_id = Auth::id();
        $parts = ChatParticipant::where('user_id', $user_id)->where('is_here', 0)->get();
        foreach($parts as $part){
            Message::where('chat_id', $part->chat_id)->where('status', 'sent')->update(['status' => 'delivered']);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function HeadChatRemoveIsHere(){
        $user_id = Auth::id();
        $parts = ChatParticipant::where('user_id', $user_id)->get();

        if ($parts->isNotEmpty()) {
            foreach ($parts as $part) {
                $part->update(['is_here' => 0]);
            }
        }
    }

//endregion

}
