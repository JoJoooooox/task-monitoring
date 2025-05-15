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
use App\Models\Task_solo;
use App\Models\Task;
use App\Models\Task_fields;
use App\Models\Task_inputs;
use App\Models\Task_pages;
use App\Models\Notification;
use App\Models\Task_submit_data;
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
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use DateTime;


class InternController extends Controller
{
    public function InternDashboardGetAllNotes(){
        $notes = NotesTask::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'exist',
            'notes' => $notes
        ]);
    }

    public function InternDashboardGetAllChats(){
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

    public function InternDashboardGetAllFeedback(){
        $feed = Feedback::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'exist',
            'feed' => $feed
        ]);
    }

    public function InternDashboardGetAllNotification(Request $request){
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

    public function InternDashboardMarkAsReadedNotification(Request $request){
        Notification::where('user_id', Auth::id())->where('is_read', 0)->update([
            'is_read' => $request->read
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function InterDashboardClearNotification(Request $request){
        Notification::where('user_id', Auth::id())->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function InternDashboardSetReviewedFeed(Request $request){
        $feed = $request->feed;
        $getFeed = Feedback::find($feed);
        if(!$getFeed){
            return response()->json([
                'status' => 'error',
            ]);
        }
        $getFeed->status = 'Reviewed';
        $getFeed->save();
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function InternDashboardRemoveFeed(Request $request){
        $feed_id = $request->feed;
        $feed = Feedback::find($feed_id);
        if (!$feed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found'
            ]);
        }

        $feed->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function InternDashboard(){
        $member = Member::where('user_id', Auth::user()->id)->first();
        $timezone = "Asia/Manila";

        // Get `PerDepartment` ranking (this is always needed)
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

        if (!$member) {
            // If no `member`, return only `PerDepartment`
            return view('intern.index', compact('PerDepartment'));
        }

        // If `member` exists, retrieve all data
        $department = Department::where('id', $member->department_id)->get();

        $temp = Task_templates::where('department_id', $member->department_id)->get();
        foreach ($temp as $row) {
            $depart = Department::find($row->department_id);
            $row->department_name = $depart ? $depart->name : 'No Department';
        }

        $ongoing = Task::where('department_id', $member->department_id)->whereIn('status', ['Ongoing', 'Overdue'])->get();
        $tocheck = Task::where('department_id', $member->department_id)->where('status', 'To Check')->get();
        $complete = Task::where('department_id', $member->department_id)->where('status', 'Completed')->get();

        $userId = Auth::user()->id;
        $soloTaskIds = Task_solo::where('user_id', $userId)->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', $userId)->pluck('task_id')->toArray();
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $myOngoing = Task::whereIn('id', $taskIds)->whereIn('status', ['Ongoing', 'Overdue'])->get();

        // Get Daily, Weekly, and Monthly Top 3 rankings
        $dailyTop3 = Task_submit_data::select(
            'users.id as user_id',
            'users.name',
            'users.username',
            'users.photo',
            'departments.name as department_name',
            DB::raw('COUNT(table_task_submit_data.id) as task_count')
        )
        ->join('users', 'table_task_submit_data.user_id', '=', 'users.id')
        ->join('departments', 'table_task_submit_data.department_id', '=', 'departments.id')
        ->where('table_task_submit_data.department_id', $member->department_id)
        ->whereDate('table_task_submit_data.created_at', Carbon::today($timezone))
        ->groupBy('users.id', 'users.name', 'users.username', 'users.photo', 'departments.name')
        ->orderByDesc('task_count')
        ->limit(3)
        ->get();

        $weeklyTop3 = Task_submit_data::select(
            'users.id as user_id',
            'users.name',
            'users.username',
            'users.photo',
            'departments.name as department_name',
            DB::raw('COUNT(table_task_submit_data.id) as task_count')
        )
        ->join('users', 'table_task_submit_data.user_id', '=', 'users.id')
        ->join('departments', 'table_task_submit_data.department_id', '=', 'departments.id')
        ->where('table_task_submit_data.department_id', $member->department_id)
        ->whereBetween('table_task_submit_data.created_at', [Carbon::now($timezone)->startOfWeek(), Carbon::now($timezone)->endOfWeek()])
        ->groupBy('users.id', 'users.name', 'users.username', 'users.photo', 'departments.name')
        ->orderByDesc('task_count')
        ->limit(3)
        ->get();

        $monthlyTop3 = Task_submit_data::select(
            'users.id as user_id',
            'users.name',
            'users.username',
            'users.photo',
            'departments.name as department_name',
            DB::raw('COUNT(table_task_submit_data.id) as task_count')
        )
        ->join('users', 'table_task_submit_data.user_id', '=', 'users.id')
        ->join('departments', 'table_task_submit_data.department_id', '=', 'departments.id')
        ->where('table_task_submit_data.department_id', $member->department_id)
        ->whereMonth('table_task_submit_data.created_at', Carbon::now($timezone)->month)
        ->groupBy('users.id', 'users.name', 'users.username', 'users.photo', 'departments.name')
        ->orderByDesc('task_count')
        ->limit(3)
        ->get();
        return view('intern.index', compact(
            'temp',
            'ongoing',
            'tocheck',
            'complete',
            'myOngoing',
            'dailyTop3',
            'weeklyTop3',
            'monthlyTop3',
            'PerDepartment'
        ));

        return view('admin.index', [
            'temp' => $temp,
            'ongoing' => $ongoing,
            'tocheck' => $tocheck,
            'complete' => $complete,
            'PerDepartment' => $PerDepartment,
            'myOngoing' => $myOngoing,
            'dailyTop3' => $dailyTop3,
            'weeklyTop3'  => $weeklyTop3,
            'monthlyTop3' => $monthlyTop3,
        ]);
    }

    public function reloadMyOngoingDiv(Request $request){
        // Retrieve all tasks from the database.
        $userId = Auth::user()->id;

        $soloTaskIds = Task_solo::where('user_id', $userId)->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', $userId)->pluck('task_id')->toArray();

        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $tasks = Task::whereIn('id', $taskIds)->whereIn('status', ['Ongoing', 'Overdue'])->get();

        foreach ($tasks as $task) {
            $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                       Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

            // Add 'checker' directly inside the task object
            $task->checker = $checker;
        }
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

                    // Ensure task is found before proceeding
                    if ($task) {
                        $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                                   Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

                        // Add 'checker' directly inside the task object
                        $task->checker = $checker;

                        $newTasks[] = $task; // Store the modified task object
                    }
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

                $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                           Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

                $updatedTask->checker = $checker;

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

    public function InternLogin(){
        if (Auth::check()) {
            return redirect('/intern/dashboard'); // Redirect if logged in
        }
        return view('user.user_login');
    }

    public function InternLogout(Request $request){
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/user/login');
    }

    public function InternProfile(){
        $id = Auth::user()->id;
        $profile = User::find($id);

        return view('intern.intern_profile', compact('profile'));
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

    //region Personal Table
    public function InternPersonalTable(){
        $taskIds = DB::table('tasks_group')
            ->where('user_id', Auth::id())
            ->pluck('task_id')
            ->merge(
                DB::table('tasks_solo')
                    ->where('user_id', Auth::id())
                    ->pluck('task_id')
            )
            ->unique();

        // Main tasks with specific statuses
        $tasks = Task::whereIn('id', $taskIds)
                    ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        foreach($tasks as $row) {
            $personal = PersonalTask::where('task_id', $row->id)->get();
            if($personal->isNotEmpty()) {
                foreach($personal as $exist) {
                    if($exist->type == 'tag') {
                        $row->is_tagged = 1;
                    }
                    if($exist->type == 'important') {
                        $row->is_important = 1;
                    }
                    if($exist->type == 'favorites') {
                        $row->is_favorites = 1;
                    }
                }
            }
        }

        // Get favorites with status filter
        $personalFavorites = PersonalTask::where('user_id', Auth::id())
            ->where('type', 'favorites')
            ->orderBy('sort', 'asc')
            ->with(['task' => function($query) {
                $query->whereIn('status', ['Ongoing', 'Overdue', 'To Check']);
            }])
            ->get();

        $favorites = $personalFavorites->map(function ($personalTask) {
            return $personalTask->task;
        })->filter(); // Filter removes null values from tasks that didn't meet status criteria

        // Get important with status filter
        $personalImportant = PersonalTask::where('user_id', Auth::id())
            ->where('type', 'important')
            ->orderBy('sort', 'asc')
            ->with(['task' => function($query) {
                $query->whereIn('status', ['Ongoing', 'Overdue', 'To Check']);
            }])
            ->get();

        $important = $personalImportant->map(function ($personalTask) {
            return $personalTask->task;
        })->filter();

        // Get tags with status filter
        $personalTags = PersonalTask::where('user_id', Auth::id())
            ->where('type', 'tag')
            ->orderBy('sort', 'asc')
            ->with(['task' => function($query) {
                $query->whereIn('status', ['Ongoing', 'Overdue', 'To Check']);
            }])
            ->get();

        $tag = $personalTags->map(function ($personalTask) {
            return $personalTask->task;
        })->filter();

        // Notes with status filter
        $personalNotes = NotesTask::whereIn('task_id', $taskIds)
            ->where('type', 'task')
            ->orderBy('sort', 'asc')
            ->with(['task' => function($query) {
                $query->whereIn('status', ['Ongoing', 'Overdue', 'To Check']);
            }])
            ->get()
            ->groupBy('task_id');

        // Fetch only tasks with the right status
        $relatedTasks = Task::whereIn('id', $personalNotes->keys())
            ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
            ->get()
            ->keyBy('id');

        $notesCount = NotesTask::where('user_id', Auth::id())->count();

        $personalPrivateNotes = NotesTask::where('user_id', Auth::id())
            ->where('type', 'private')
            ->orderBy('sort', 'asc')
            ->get();

        return view('intern.intern_personal_table', compact(
            'tasks', 'favorites', 'important', 'tag',
            'personalNotes', 'relatedTasks', 'notesCount', 'personalPrivateNotes'
        ));
    }

    public function InternPersonalTableSort(Request $request){
        $sort = $request->input('sort', 'due'); // Default sort by date

        $taskIds = DB::table('tasks_group')
            ->where('user_id', Auth::id())
            ->pluck('task_id') // Get task IDs from tasks_group
            ->merge(
                DB::table('tasks_solo')
                    ->where('user_id', Auth::id())
                    ->pluck('task_id') // Get task IDs from tasks_solo
            )
            ->unique(); // Remove duplicates

        $tasks = Task::whereIn('id', $taskIds)->whereIn('status', ['Ongoing', 'Overdue'])->when($sort, function($query) use ($sort) {
            if ($sort === 'progress_percentage') {
                return $query->orderByDesc($sort);
            }
            return $query->orderBy($sort);
        })->get();
        foreach($tasks as $row){
            $personal = PersonalTask::where('task_id', $row->id)->get();
            if($personal->isNotEmpty()){
                foreach($personal as $exist){
                    if($exist->type == 'tag'){
                        $row->is_tagged = 1;
                    }

                    if($exist->type == 'important'){
                        $row->is_important = 1;
                    }

                    if($exist->type == 'favorites'){
                        $row->is_favorites = 1;
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'tasks' => $tasks
        ]);
    }

    public function InternPersonalTableMarkTask(Request $request){
        // Assume you are using authentication; otherwise, set $userId appropriately
        $user_id = Auth::id();

        // Get the type (e.g., 'favorites', 'important', or 'tag') and task IDs from the request
        $type = $request->input('type');
        $task_ids = $request->input('task_ids', []);

        // Validate required fields
        if (empty($task_ids) || empty($type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please check a row before marking it'
            ], 400);
        }

        // Check for existing tasks
        $existingTasks = PersonalTask::where('user_id', $user_id)
            ->where('type', $type)
            ->whereIn('task_id', $task_ids)
            ->pluck('task_id')
            ->toArray();

        // Filter out duplicates
        $newTaskIds = array_diff($task_ids, $existingTasks);

        if (empty($newTaskIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'All selected tasks already exist in ' . $type . ' type'
            ]);
        }

        // Get current max sort value for the type
        $maxSort = PersonalTask::where('user_id', $user_id)
            ->where('type', $type)
            ->max('sort') ?? 0;

        // Prepare data for insertion
        $dataToInsert = [];
        $currentSort = $maxSort + 1;

        foreach ($newTaskIds as $task_id) {
            $dataToInsert[] = [
                'user_id' => $user_id,
                'task_id' => $task_id,
                'type' => $type,
                'sort' => $currentSort++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert
        PersonalTask::insert($dataToInsert);

        // Prepare response message
        $message = count($newTaskIds) . ' task(s) added to ' . $type . ' successfully.';
        $duplicates = [];

        if (count($existingTasks) > 0) {
            $duplicates = $existingTasks;
            $message .= ' ' . count($existingTasks) . ' task(s) already existed.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'duplicates' => $duplicates
        ]);
    }

    public function InternPersonalTableUpdateSort(Request $request){
        $user = Auth::id();
        $type = $request->input('type');
        if($type == 'task'){
            $task_id = $request->input('task_id');
            try {
                $sortedIds = $request->input('sorted_notes_ids');
                DB::transaction(function () use ($user, $task_id, $sortedIds) {
                    foreach ($sortedIds as $index => $notesId) {
                        NotesTask::where('user_id', $user)
                            ->where('task_id', $task_id)
                            ->where('id', $notesId)
                            ->update(['sort' => $index + 1]);
                    }
                });

                return response()->json(['status' => 'success']);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error updating sort order: ' . $e->getMessage()
                ], 500);
            }
        } else if($type == 'private') {
            try {
                $sortedIds = $request->input('sorted_ids');
                DB::transaction(function () use ($user, $type, $sortedIds) {
                    foreach ($sortedIds as $index => $notesId) {
                        NotesTask::where('user_id', $user)
                            ->where('type', $type)
                            ->where('id', $notesId)
                            ->update(['sort' => $index + 1]);
                    }
                });

                return response()->json(['status' => 'success']);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error updating sort order: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $sortedIds = $request->input('sorted_ids');

            try {
                DB::transaction(function () use ($user, $type, $sortedIds) {
                    foreach ($sortedIds as $index => $taskId) {
                        PersonalTask::where('user_id', $user)
                            ->where('type', $type)
                            ->where('task_id', $taskId)
                            ->update(['sort' => $index + 1]);
                    }
                });

                return response()->json(['status' => 'success']);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error updating sort order: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function InternPersonalTableTaskFavorites(Request $request){
        $task_id = $request->task;
        $isTrue = ($request->is_fav === 'yes' ? 1 : 0);
        $type = 'favorites';

        return $this->SetPersonal($task_id, $type, $isTrue);
    }

    public function InternPersonalTableTaskImportant(Request $request){
        $task_id = $request->task;
        $isTrue = ($request->is_important == 'yes' ? 1 : 0);
        $type = 'important';

        return $this->SetPersonal($task_id, $type, $isTrue);
    }

    public function InternPersonalTableTaskTag(Request $request){
        $task_id = $request->task;
        $isTrue = ($request->is_tag == 'yes' ? 1 : 0);
        $type = 'tag';
        return $this->SetPersonal($task_id, $type, $isTrue);
    }

    private function SetPersonal($task_id, $type, $isTrue){
        $user_id = Auth::id();

        if ($isTrue == 0) {
            // Add task to the end of the sort order
            if (!Task::where('id', $task_id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Task does not exist'
                ]);
            }

            // Check if already exists in personal tasks
            if (PersonalTask::where('user_id', $user_id)
                ->where('type', $type)
                ->where('task_id', $task_id)
                ->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Task already exists in '.$type
                ]);
            }
            $maxSort = PersonalTask::where('user_id', $user_id)
                ->where('type', $type)
                ->max('sort') ?? 0;

            PersonalTask::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'task_id' => $task_id,
                    'type' => $type
                ],
                ['sort' => $maxSort + 1]
            );
        } else {
            // Remove task and reorganize sort order
            $task = PersonalTask::where('user_id', $user_id)
                ->where('type', $type)
                ->where('task_id', $task_id)
                ->first();

            if (!$task) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Task not found in '.$type
                ]);
            }

            $deletedSort = $task->sort;
            $task->delete();

            // Reorganize remaining items
            PersonalTask::where('user_id', $user_id)
                ->where('type', $type)
                ->where('sort', '>', $deletedSort)
                ->decrement('sort');
        }

        return response()->json(['status' => 'success']);
    }

    public function InternPersonalTableTaskNotes(Request $request){
        $user_id = Auth::id();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:225',
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
        $task_id = $request->task;
        $title = $request->title;
        $notes = $request->notes;
        $type = 'task';

        if (!Task::where('id', $task_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task does not exist'
            ]);
        }

        // Check if already exists in personal tasks
        $maxSort = NotesTask::where('user_id', $user_id)
            ->where('task_id', $task_id)
            ->where('type', $type)
            ->max('sort') ?? 0;

        NotesTask::create([
            'user_id' => $user_id,
            'task_id' => $task_id,
            'title' => $title,
            'notes' => $notes,
            'type' => $type,
            'sort' => $maxSort + 1
        ]);

        return response()->json(['status' => 'success']);
    }

    public function InternPersonalTableTaskNotesRemove(Request $request){
        $task_id = $request->task;
        $note_id = $request->notes;

        $note = NotesTask::where('id', $note_id)
                ->where('task_id', $task_id)
                ->first();

        if (!$note) {
            return response()->json([
                'status' => 'error',
                'message' => 'Note\'s not found'
            ]);
        }

        if(intval($note->user_id) != intval(Auth::id())){
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete this note\'s'
            ]);
        }

        $deletedSort = $note->sort;
        $note->delete();

        // Reorganize remaining items
        NotesTask::where('id', $note_id)
            ->where('task_id', $task_id)
            ->where('sort', '>', $deletedSort)
            ->decrement('sort');

        return response()->json(['status' => 'success']);
    }

    public function InternPersonalTableNotesRemove(Request $request){
        $note_id = $request->notes;

        if(isset($request->dashboard)){
            $getNotes = NotesTask::find($note_id);
            if($getNotes->task_id == null){
                $note = NotesTask::where('id', $note_id)
                    ->where('type', 'private')
                    ->first();

                if (!$note) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Note\'s not found'
                    ]);
                }

                if($note->user_id != Auth::id()){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot delete this note\'s'
                    ]);
                }

                $deletedSort = $note->sort;
                $note->delete();

                // Reorganize remaining items
                NotesTask::where('id', $note_id)
                    ->where('type', 'private')
                    ->where('sort', '>', $deletedSort)
                    ->decrement('sort');

                return response()->json(['status' => 'success']);
            }

            $note = NotesTask::where('id', $note_id)
            ->where('task_id', $getNotes->task_id)
            ->first();

            if (!$note) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Note\'s not found'
                ]);
            }

            if(intval($note->user_id) != intval(Auth::id())){
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot delete this note\'s'
                ]);
            }

            $deletedSort = $note->sort;
            $note->delete();

            // Reorganize remaining items
            NotesTask::where('id', $note_id)
                ->where('task_id', $getNotes->task_id)
                ->where('sort', '>', $deletedSort)
                ->decrement('sort');

            return response()->json(['status' => 'success']);
        }

        $note = NotesTask::where('id', $note_id)
            ->where('type', 'private')
            ->first();

        if (!$note) {
            return response()->json([
                'status' => 'error',
                'message' => 'Note\'s not found'
            ]);
        }

        if($note->user_id != Auth::id()){
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete this note\'s'
            ]);
        }

        $deletedSort = $note->sort;
        $note->delete();

        // Reorganize remaining items
        NotesTask::where('id', $note_id)
            ->where('type', 'private')
            ->where('sort', '>', $deletedSort)
            ->decrement('sort');

        return response()->json(['status' => 'success']);

    }

    public function InternPersonalTableEditNotes(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:225',
            'description' => 'nullable|string|max:225'
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

        $title = $request->title;
        $notes = $request->notes;
        $description = $request->description;
        $id = $request->id;

        $note = NotesTask::find($id);
        if(!$note){
            return response()->json([
                'status' => 'error',
                'message' => "Oops! This note's might been deleted please check again"
            ]);
        }

        $note->update([
            'title' => $title,
            'description' => $description,
            'notes' => $notes
        ]);

        if(isset($request->gets)){
            $getNotes = NotesTask::find($id);
            return response()->json([
                'status' => 'success',
                'note' => $getNotes
            ]);
        } else {
            return response()->json(['status' => 'success']);
        }
    }

    public function InternPersonalTableCreateNotes(Request $request){
        $user_id = Auth::id();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:225',
            'description' => 'nullable|string|max:225',
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

        $description = $request->description;
        $title = $request->title;
        $notes = $request->notes;
        $type = 'private';

        $maxSort = NotesTask::where('user_id', $user_id)
            ->where('type', $type)
            ->max('sort') ?? 0;

        NotesTask::create([
            'user_id' => $user_id,
            'title' => $title,
            'notes' => $notes,
            'description' => $description,
            'type' => $type,
            'sort' => $maxSort + 1
        ]);

        return response()->json(['status' => 'success']);
    }
//endregion

//region Task
    public function InternTasks(){

        $userId = Auth::user()->id;

        // Get task IDs from solo and group tasks
        $soloTaskIds = Task_solo::where('user_id', $userId)->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', $userId)->pluck('task_id')->toArray();

        // Merge task IDs and remove duplicates
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));

        // Fetch the tasks using the collected task IDs and filter for ongoing/overdue
        $tasks = Task::whereIn('id', $taskIds)
                     ->whereIn('status', ['Ongoing', 'Overdue'])
                     ->orderBy('created_at', 'desc')
                     ->get();

        $tocheck = Task::whereIn('id', $taskIds)
                     ->whereIn('status', ['To Check'])
                     ->where('is_archived', 0)
                     ->orderBy('created_at', 'desc')
                     ->get();

        $complete = Task::whereIn('id', $taskIds)
                     ->whereIn('status', ['Completed'])
                     ->where('is_archived', 0)
                     ->orderBy('created_at', 'desc')
                     ->get();


        return view('intern.intern_tasks', compact('tasks', 'tocheck', 'complete'));
    }

    public function reloadOngoingDiv(Request $request){
        // Retrieve all tasks from the database.
        $soloTaskIds = Task_solo::where('user_id', Auth::id())->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', Auth::id())->pluck('task_id')->toArray();

        // Merge task IDs and remove duplicates
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $tasks = Task::whereIn('status', ['Ongoing', 'Overdue'])->whereIn('id', $taskIds)->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

        foreach ($tasks as $task) {
            $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                       Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

            // Add 'checker' directly inside the task object
            $task->checker = $checker;
        }
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

                    // Ensure task is found before proceeding
                    if ($task) {
                        $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                                   Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

                        // Add 'checker' directly inside the task object
                        $task->checker = $checker;

                        $newTasks[] = $task; // Store the modified task object
                    }
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

                $checker = Task_solo::where('user_id', Auth::id())->where('task_id', $task->id)->exists() ||
                           Task_group::where('user_id', Auth::id())->where('task_id', $task->id)->exists();

                $updatedTask->checker = $checker;

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
        $soloTaskIds = Task_solo::where('user_id', Auth::id())->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', Auth::id())->pluck('task_id')->toArray();

        // Merge task IDs and remove duplicates
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $tasks = Task::where('status', 'To Check')->whereIn('id', $taskIds)->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

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
        $soloTaskIds = Task_solo::where('user_id', Auth::id())->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', Auth::id())->pluck('task_id')->toArray();

        // Merge task IDs and remove duplicates
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $tasks = Task::where('status', 'Completed')->whereIn('id', $taskIds)->where('is_archived', 0)->orderBy('created_at', 'desc')->get();

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

    public function InternTasksRequestOvertimeTask(Request $request){
        $task_id = $request->task;

        $task = Task::where('id', $task_id)
        ->where('user_status', 'Sleep')
        ->whereIn('status', ['Overdue', 'Ongoing'])
        ->first();

        if ($task) {
            if($task->type == "Group"){
                $group = Task_group::where('task_id', $task->id)->get();
                if($group->isNotEmpty()){
                    foreach($group as $member){
                        $status = new Task_user_status();
                        $status->task_id = $task->id;
                        $status->user_id = $member->user_id;
                        $status->user_status = 'Request Overtime';
                        $status->save();
                    }
                }
            } else if($task->type == "Solo"){
                $solo = Task_solo::where('task_id', $task->id)->first();
                if($solo){
                    $status = new Task_user_status();
                    $status->task_id = $task->id;
                    $status->user_id = $solo->user_id;
                    $status->user_status = 'Request Overtime';
                    $status->save();
                }
            }
            $task->update(['user_status' => 'Request Overtime']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Task is not existing as sleep']);
        }

        return response()->json(['status' => 'success']);
    }

    public function InternEditTasks($task){
        $info = Task::find($task);

        if(empty($info)) {
            abort(404);
        }

        if($info->type === 'Solo'){
            $check = Task_solo::where('task_id', $info->id)->where('user_id', Auth::user()->id)->exists();
            if(!$check){
                abort(404);
            }
        } else if($info->type === 'Group') {
            $check = Task_group::where('task_id', $info->id)->where('user_id', Auth::user()->id)->exists();
            if(!$check){
                abort(404);
            }
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

        $linkedInfo = Task::find($info->link_id);

        $pagesLinked = Task_pages::where('task_id', $info->link_id)->get();

        $pagesLinkedWithContent = $pagesLinked->map(function ($page) {
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

        $inputLinkedValues = collect(); // Initialize an empty collection

        foreach ($pagesLinkedWithContent as $row) {
            if (!empty($row['contents'])) {
                foreach ($row['contents'] as $content) {
                    $taskInputs = Task_inputs::where('field_id', $content->id)
                    ->whereNotNull('value')
                    ->get();
                    $inputLinkedValues = $inputLinkedValues->merge($taskInputs);
                }
            }
        }

        return view('intern.intern_etasks', compact('info', 'pages', 'pagesWithContent', 'inputValues', 'pagesLinked', 'pagesLinkedWithContent', 'inputLinkedValues', 'linkedInfo'));
    }

    public function InternEditSaveTasks(Request $request){
        $missingRequiredFields = [];
        if($request->from !== 'save'){
            foreach ($request->except('from') as $fieldName => $value) {
                // Extract field_id and task_id using regex
                if (preg_match('/_(\d+)_(\d+)$/', $fieldName, $matches)) {
                    $fieldId = $matches[1]; // Extract field_id
                    $taskId = $matches[2];  // Extract task_id

                    $field = Task_fields::find($fieldId);
                    if ($field && $field->is_required == 1 && empty($value)) {
                        // If a required field is empty, add it to the missing list
                        $missingRequiredFields[] = $fieldId;
                    }
                }
            }

            // If there are missing required fields, stop the process and return an error
            if (!empty($missingRequiredFields)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Some required fields are missing!',
                    'missing_fields' => $missingRequiredFields
                ]);
            }
        }

        // If all required fields are filled, proceed with saving
        foreach ($request->except('from') as $fieldName => $value) {
            if (preg_match('/_(\d+)_(\d+)$/', $fieldName, $matches)) {
                $fieldId = $matches[1];
                $taskId = $matches[2];

                if ($request->hasFile($fieldName)) {

                    $file = $request->file($fieldName);

                    // Validate file size (5MB limit)
                    if ($file->getSize() > 5 * 1024 * 1024) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'File size must be less than 5MB!'
                        ]);
                    }

                    $existingFile = Task_inputs::where('task_id', $taskId)
                    ->where('field_id', $fieldId)
                    ->value('value');

                    if ($existingFile && file_exists(public_path($existingFile))) {
                        unlink(public_path($existingFile)); // Delete the file
                    }

                    // Generate a custom filename
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // Get filename without extension
                    $extension = $file->getClientOriginalExtension(); // Get file extension
                    $customFileName = 'task_' . $taskId . '_field_' . $fieldId . '_' . time() . '.' . $extension; // Custom filename

                    // Define the public upload directory
                    $uploadPath = public_path('upload');

                    // Ensure the upload directory exists
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true); // Create the directory if it doesn't exist
                    }

                    // Move the file to the public/upload directory
                    $file->move($uploadPath, $customFileName);

                    // Save file path to the database
                    Task_inputs::updateOrCreate(
                        ['task_id' => $taskId, 'field_id' => $fieldId],
                        ['value' => 'upload/' . $customFileName] // Save relative path to database
                    );
                } else {
                    // Save non-file data
                    Task_inputs::updateOrCreate(
                        ['task_id' => $taskId, 'field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }
        }

        $totalFields = Task_fields::where('task_id', $taskId)->count();
        $filledFields = Task_inputs::where('task_id', $taskId)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();
        $progressPercentage = ($totalFields > 0) ? round(($filledFields / $totalFields) * 100, 2) : 0;
        Task::where('id', $taskId)->update(['progress_percentage' => $progressPercentage]);

        if($request->from !== 'finish'){
            return response()->json([
                'status'  => 'success',
                'message' => 'Task inputs saved successfully!'
            ]);
        } else if ($request->from === 'finish'){
            $taskId = null;
            foreach ($request->except('from') as $fieldName => $value) {
                // Extract field_id and task_id using regex
                if (preg_match('/_(\d+)_(\d+)$/', $fieldName, $matches)) {
                    $fieldId = $matches[1]; // Extract field_id
                    $taskId = $matches[2];  // Extract task_id

                }
            }

            if($taskId){
                $info = Task::find($taskId);
                if($info->type === 'Group'){
                    $member = Task_group::where('task_id', $taskId)->get();
                    foreach($member as $row){
                        $data = new Task_submit_data();
                        $data->user_id = $row->user_id;
                        $data->task_id = $row->task_id;
                        $data->department_id = $info->department_id;
                        $data->status = ($info->status == 'Overdue' ? 'Overdue' : 'Ongoing');
                        $data->save();

                        if($row->user_id !== Auth::user()->id){
                            $notif = new Notification();
                            $notif->user_id = $user;
                            $notif->message = 'User: '.Auth::user()->name.' submit the group task Task Title: '.$info->title;
                            $notif->type = 'success';
                            $notif->save();
                        }
                    }

                    $log = new Log();
                    $log->name = Auth::user()->name;
                    $log->action = "Submit Group Task: {$info->title} User: ".Auth::user()->name." ID: ".Auth::user()->id."";
                    $log->description = date('Y-m-d');
                    $log->save();
                } else {
                    $data = new Task_submit_data();
                    $data->user_id = Auth::user()->id;
                    $data->task_id = $taskId;
                    $data->department_id = $info->department_id;
                    $data->status = $info->status;
                    $data->save();

                    $log = new Log();
                    $log->name = Auth::user()->name;
                    $log->action = "Submit Solo Task: {$info->title} User: ".Auth::user()->name." ID: ".Auth::user()->id."";
                    $log->description = date('Y-m-d');
                    $log->save();
                }

                $toCheck = Task::find($taskId);
                $toCheck->status = 'To Check';
                $toCheck->save();
            }

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Task marked as "To Check"',
                    'redirect' => route('intern.tasks') // Pass redirect URL
                ]);
            }

            session()->flash('success', 'Task marked as "To Check"');

            return redirect()->route('intern.tasks');
        }
    }

    public function InternEditUserStatusCheckingTasks(Request $request){
        $statusMap = [
            'idle' => 'Idle',
            'not working' => 'Away',
            'working' => 'Active'
        ];

        $formattedStatus = $statusMap[$request->user_status] ?? $request->user_status;

        Task_user_status::create([
            'task_id' => $request->task_id,
            'user_id' => Auth::user()->id,
            'user_status' => $formattedStatus,
        ]);

        $update = Task::find($request->task_id);
        $update->user_status = $formattedStatus;
        $update->save();

        return response()->json(['status' => 'success']);
    }

    public function InternLiveViewTasks($task){
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

        return view('intern.intern_lvtasks', compact('info', 'task_id', 'inputValues', 'pages', 'pagesWithContent'));
    }

    public function InternGetLiveViewTasks(Request $request){
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

    public function InternTasksGetRadio(Request $request){
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

    public function InternTasksGetDown(Request $request){
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

    public function InternTasksLiveReloading(Request $request){
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

    public function InternPrintTasks($task){
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

        return view('intern.intern_ptasks', compact('info', 'task_id', 'inputValues', 'pages', 'pagesWithContent'));
    }

    public function InternGetPrintTasks(Request $request){
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

    public function InternTasksPrintGetRadio(Request $request){
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

    public function InternTasksPrintGetDown(Request $request){
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

    public function InternTasksLivePrintReloading(Request $request){
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
//endregion

//region Department
    public function InternDepartment(){
        $myDept = Member::where('user_id', Auth::user()->id)->first();
        $dept = '';
        if($myDept){
            $dept = Department::find($myDept->department_id);

            if($dept) {
                $dept->dept_member = Member::join('users', 'members.user_id', '=', 'users.id')
                    ->leftJoin('tasks_group', 'members.user_id', '=', 'tasks_group.user_id')
                    ->leftJoin('tasks_solo', 'members.user_id', '=', 'tasks_solo.user_id')
                    ->leftJoin('tasks', function ($join) {
                        $join->on('tasks_group.task_id', '=', 'tasks.id')
                            ->orOn('tasks_solo.task_id', '=', 'tasks.id'); // Correct way to handle OR condition in join
                    })
                    ->leftJoin('table_task_submit_data', 'table_task_submit_data.user_id', '=', 'users.id')
                    ->leftJoin('task_user_status', 'task_user_status.user_id', '=', 'users.id')
                    ->where('members.department_id', $dept->id)
                    ->whereIn('members.role', ['employee', 'intern'])
                    ->groupBy(
                        'members.id', 'members.user_id', 'members.department_id',
                        'members.role', 'members.name', 'members.status', 'members.profile', // Add missing columns
                        'users.photo', 'users.is_online'
                    ) // Group by member ID
                    ->selectRaw('members.id, members.user_id, members.department_id, members.role,
                    members.name, members.status, members.profile, users.photo, users.is_online,
                    COALESCE(
                        JSON_ARRAYAGG(DISTINCT IF(tasks.id IS NOT NULL,
                            JSON_OBJECT(
                                "task_id", tasks.id,
                                "task_title", tasks.title,
                                "task_status", tasks.status,
                                "task_progress", tasks.progress_percentage
                            ),
                            NULL)
                        ),
                        "[]"
                    ) as tasks,
                    COUNT(DISTINCT table_task_submit_data.id) as completed,
                    JSON_ARRAYAGG(DISTINCT JSON_OBJECT(
                        "status_id", task_user_status.id,
                        "status_type", task_user_status.user_status,
                        "status_created_at", task_user_status.created_at
                    )) as statuses')
                    ->get();


                $dept->dept_head = Member::join('users', 'members.user_id', '=', 'users.id')
                    ->leftJoin('tasks_group', 'members.user_id', '=', 'tasks_group.user_id')
                    ->leftJoin('tasks_solo', 'members.user_id', '=', 'tasks_solo.user_id')
                    ->leftJoin('tasks', function ($join) {
                        $join->on('tasks_group.task_id', '=', 'tasks.id')
                            ->orOn('tasks_solo.task_id', '=', 'tasks.id'); // Correct way to handle OR condition in join
                    })
                    ->leftJoin('table_task_submit_data', 'table_task_submit_data.user_id', '=', 'users.id')
                    ->leftJoin('task_user_status', 'task_user_status.user_id', '=', 'users.id')
                    ->where('members.department_id', $dept->id)
                    ->where('members.role', 'observer')
                    ->groupBy(
                        'members.id', 'members.user_id', 'members.department_id',
                        'members.role', 'members.name', 'members.status', 'members.profile', // Add missing columns
                        'users.photo', 'users.is_online'
                    ) // Group by member ID
                    ->selectRaw('members.id, members.user_id, members.department_id, members.role,
                    members.name, members.status, members.profile, users.photo, users.is_online,
                    COALESCE(
                        JSON_ARRAYAGG(DISTINCT IF(tasks.id IS NOT NULL,
                            JSON_OBJECT(
                                "task_id", tasks.id,
                                "task_title", tasks.title,
                                "task_status", tasks.status,
                                "task_progress", tasks.progress_percentage
                            ),
                            NULL)
                        ),
                        "[]"
                    ) as tasks,
                    COUNT(DISTINCT table_task_submit_data.id) as completed,
                    JSON_ARRAYAGG(DISTINCT JSON_OBJECT(
                        "status_id", task_user_status.id,
                        "status_type", task_user_status.user_status,
                        "status_created_at", task_user_status.created_at
                    )) as statuses')
                    ->get();
            }

            foreach ($dept->dept_head as $head) {
                $head->tasks = json_decode($head->tasks) ?? [];
                $head->completed = json_decode($head->completed) ?? 0;

                // Filter out null tasks
                $head->tasks = array_filter($head->tasks, function($task) {
                    return $task !== null;
                });
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

                if (!empty($head->statuses)) {
                    $statuses = collect(json_decode($head->statuses));

                    $statuses = $statuses->filter(function ($status) {
                        return !is_null($status->status_type);
                    });

                    if ($statuses->isEmpty()) {
                        $onlyEmergencyOrSleep = true;
                    } else {
                        foreach ($statuses as $index => $status) {
                            $nextTimestamp = isset($statuses[$index + 1]) ? $statuses[$index + 1]->status_created_at : now();

                            $start = Carbon::parse($status->status_created_at);
                            $end = Carbon::parse($nextTimestamp);
                            $duration = $start->diffInSeconds($end);

                            if (array_key_exists($status->status_type, $totalDurations)) {
                                $totalDurations[$status->status_type] += $duration;
                            }

                            $statusDurations[] = [
                                'user_status' => $status->status_type,
                                'duration' => $duration // Store in seconds
                            ];

                            if ($status->status_type !== 'Emergency' && $status->status_type !== 'Sleep') {
                                $onlyEmergencyOrSleep = false;
                            }
                        }
                    }
                }

                $head->isParticipating = !empty($head->statuses) && !$onlyEmergencyOrSleep;

                foreach ($totalDurations as $status => $seconds) {
                    $totalDurations[$status] = gmdate("H:i:s", $seconds);
                }

                $head->totalDuration = $totalDurations;
                $head->statusDurations = $statusDurations;
            }


            foreach ($dept->dept_member as $member) {
                $member->tasks = json_decode($member->tasks) ?? [];
                $member->completed = json_decode($member->completed) ?? 0;

                // Filter out null tasks
                $member->tasks = array_filter($member->tasks, function($task) {
                    return $task !== null;
                });
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

                if (!empty($member->statuses)) {
                    $statuses = collect(json_decode($member->statuses));

                    $statuses = $statuses->filter(function ($status) {
                        return !is_null($status->status_type);
                    });


                    if ($statuses->isEmpty()) {
                        $onlyEmergencyOrSleep = true;
                    } else {
                        foreach ($statuses as $index => $status) {
                            $nextTimestamp = isset($statuses[$index + 1]) ? $statuses[$index + 1]->status_created_at : now();

                            $start = Carbon::parse($status->status_created_at);
                            $end = Carbon::parse($nextTimestamp);
                            $duration = $start->diffInSeconds($end);

                            if (array_key_exists($status->status_type, $totalDurations)) {
                                $totalDurations[$status->status_type] += $duration;
                            }

                            $statusDurations[] = [
                                'user_status' => $status->status_type,
                                'duration' => $duration // Store in seconds
                            ];

                            if ($status->status_type !== 'Emergency' && $status->status_type !== 'Sleep') {
                                $onlyEmergencyOrSleep = false;
                            }
                        }
                    }
                }
                $member->isParticipating = !empty($member->statuses) && !$onlyEmergencyOrSleep;

                foreach ($totalDurations as $status => $seconds) {
                    $totalDurations[$status] = gmdate("H:i:s", $seconds);
                }

                $member->totalDuration = $totalDurations;
                $member->statusDurations = $statusDurations;
            }
        }


        return view('intern.intern_department', compact('dept'));
    }
//endregion

//region Calendar

public function InternCalendar(){
    return view('intern.intern_calendar');
}

public function InternCalendarViewTaskDate(){
    $member = Member::where('user_id', Auth::user()->id)->first();
    $events = [];
    if($member){
        $soloTaskIds = Task_solo::where('user_id', Auth::id())->pluck('task_id')->toArray();
        $groupTaskIds = Task_group::where('user_id', Auth::id())->pluck('task_id')->toArray();

        // Merge task IDs and remove duplicates
        $taskIds = array_unique(array_merge($soloTaskIds, $groupTaskIds));
        $task = Task::whereIn('id', $taskIds )->whereIn('status', ['Ongoing', 'Overdue'])->get();
        foreach($task as $row){
            $department = Department::find($row->department_id);
            $users = [];
            if($row->type === 'Solo'){
                $user = Task_solo::where('task_id', $row->id)->where('user_id', Auth::id())->get();
                foreach($user as $persons){
                    $person = User::find($persons->user_id);
                    $users[] = array(
                        'user_id' => $person->id,
                        'name' => $person->name,
                        'photo' => $person->photo,
                    );
                }
            } else if($row->type === 'Group'){
                $user = Task_group::where('task_id', $row->id)->where('user_id', Auth::id())->get();
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
    } else {
        return response()->json([
            'status' => 'nothing'
        ]);
    }
}

public function InternCalendarSaveEvent(Request $request){
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

    if($request->type === 'department'){
        $member = Member::where('user_id', Auth::user()->id)->first();
        $calendar = CalendarEvent::create([
            'user_id' => Auth::id(),
            'department_id' => $member->department_id,
            'title' => $request->title,
            'type' => $request->type,
            'start' => $request->start,
            'end' => $request->end,
            'color' => trim($request->color),
            'border' => trim($request->border),
            'description' => $request->description ?? null
        ]);

        $department_member = Member::where('department_id', $member->department_id)->where('user_id', '!=', Auth::id())->get();
        if($department_member->isNotEmpty()){
            foreach($department_member as $row){
                $notif = new Notification();
                $notif->user_id = $row->user_id;
                $notif->message = 'New Department Event posted in calendar by observer: "'.Auth::user()->name.'" event title: "'.$request->title.'" date: "'.$request->start.'" kindly check it';
                $notif->type = 'info';
                $notif->save();
            }
        }

        $log = new Log();
        $log->name = Auth::user()->name;
        $log->action = "User: ".Auth::user()->name." ID: ".Auth::user()->id . " create a department event";
        $log->description = date('Y-m-d');
        $log->save();

        Log::create([
            'name' => Auth::user()->name,
            'action' => "Create New Department Event in Calendar: {$request->title} ID: {$calendar->id}",
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

public function InternCalendarViewPrivateEventDate(){
    $event = CalendarEvent::where('user_id', Auth::id())->where('type', 'private')->with('user')->get();

    if($event->isNotEmpty()){
        return response()->json($event);
    } else {
        return response()->json([
            'status' => 'nothing'
        ]);
    }
}

public function InternCalendarViewDepartmentEventDate(){
    $member = Member::where('user_id', Auth::user()->id)->first();
    $event = CalendarEvent::where('type', 'department')->where('department_id', $member->department_id)->with('user')->get();

    if($event->isNotEmpty()){
        return response()->json($event);
    } else {
        return response()->json([
            'status' => 'nothing'
        ]);
    }
}

public function InternCalendarViewAnnouncementEventDate(){
    $event = CalendarEvent::where('type', 'announcement')->with('user')->get();

    if($event->isNotEmpty()){
        return response()->json($event);
    } else {
        return response()->json([
            'status' => 'nothing'
        ]);
    }
}

public function InternCalendarRemoveEvent(Request $request){
    $calendar = CalendarEvent::where('id', $request->event)->first();
    if(!$calendar){
        return response()->json([
            'status' => 'error',
            'id' => 'This event must be removed, please check again'
        ]);
    }

    $calendarMe = CalendarEvent::where('user_id', Auth::id())->where('id', $request->event)->first();
    if(!$calendarMe){
        return response()->json([
            'status' => 'error',
            'id' => 'This event cannot be removed by you, if you want to delete this please ask the creator of this event'
        ]);
    }

    if($calendar->type == 'department'){
        Log::create([
            'name' => Auth::user()->name,
            'action' => "Delete Department Event in Calendar: {$calendar->title} ID: {$calendar->id}",
            'description' => now()->format('Y-m-d'),
        ]);
    }


    $calendar->delete();

    return response()->json([
        'status' => 'success'
    ]);
}

//endregion

//region Chat
    public function InternChat(){
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


        return view('intern.intern_chat', compact('users', 'chatResult', 'rooms', 'roomsEnd'));
    }

    public function InternChatSendContactMessage(Request $request){
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

    public function InternViewChats(Request $request){
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

    public function InternChatSendMessage(Request $request){
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

    public function InternChatCheckWhoSeen(Request $request){
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

    public function InternCheckMessageAttachment(Request $request){
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

    public function InternChatRepliedUser(Request $request){
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

    public function InterncheckMessageReply(Request $request){
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

    public function InternChatGetTaskInfo(Request $request){
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

    public function InternGetEditMessage(Request $request){
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

    public function InternChatEditMessage(Request $request){
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

    public function InternViewEditMessage(Request $request){
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

    public function InternViewMessageContact(Request $request){
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

    public function InternChatSendForwardMessage(Request $request){
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

    public function InternCheckWhoForward(Request $request){
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

    public function InternChatUnsendMessage(Request $request){
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

    public function InternChatPinMessage(Request $request){
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

    public function InternChatUnpinMessage(Request $request){
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

    public function InternChatReact(Request $request){
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

    public function InternViewPinnedMessage(Request $request){
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

    public function InternChatSetNickname(Request $request){
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

    public function InternChatSetMutedChat(Request $request){
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

    public function InternChatSearchMessageValue(Request $request) {
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

    public function InternChatSaveMeeting(Request $request) {
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

            $dept = Member::where('department_id', Auth::department()->id)->with('user')->get();
            foreach($dept as $member){
                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Observer: ' . Auth::user()->name . ' ended the meeting "' . $request->name . '" To Emergency';
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

    // No changes needed to ObserverChatRemoveMeeting

    public function InternChatRemoveMeeting(Request $request){
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

            $dept = Member::where('department_id', Auth::department()->id)->with('user')->get();
            foreach($dept as $member){
                $notif = new Notification();
                $notif->user_id = $member->user_id;
                $notif->message = 'Observer: ' . Auth::user()->name . ' ended the meeting "' . $updated->room_name . '" To Emergency';
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

    public function InternChatCreateGroupMessage(Request $request){
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

    public function InternChatSetConvoImage(Request $request){
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

    public function InternChatUnsetConvoImage(Request $request){
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

    public function InternChatSetConvoName(Request $request){
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

    public function InternChatUnsetConvoName(Request $request){
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

    public function InternChatAddNewMemberGroup(Request $request) {
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

    public function InternChatToggleAsAdmin(Request $request){
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

    public function InternChatRemoveFromGroup(Request $request){
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

    public function InternChatLeaveConversation(Request $request){
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

    public function InternChatUnsetChatIsHere(){
        $user_id = Auth::id();
        ChatParticipant::where('user_id', $user_id)->where('is_here', 1)->update([
            'is_here' => 0
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function InternChatSetChatIsHere(){
        $user_id = Auth::id();
        $parts = ChatParticipant::where('user_id', $user_id)->where('is_here', 0)->get();
        foreach($parts as $part){
            Message::where('chat_id', $part->chat_id)->where('status', 'sent')->update(['status' => 'delivered']);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function InternChatRemoveIsHere(){
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
