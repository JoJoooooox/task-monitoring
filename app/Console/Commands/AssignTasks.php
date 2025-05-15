<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Auth;
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
use App\Models\Task_user_status;
use App\Models\Feedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\PHPMailerService;
use App\Services\PhilSMSService;

class AssignTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assign tasks based on rules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->solo_task();
            $this->group_task();
            $this->due_checker();
            $this->checker_task();
            $this->distribute_task();
            $this->accept_distribute_task();
            $this->link_solo_task();
            $this->link_group_task();
            $this->set_sleep();
            $this->accept_overtime();
            $this->user_activity();
        } catch (Exception $e) {
            Log::error("Error in AssignTasks command: " . $e->getMessage());
            $this->error('Task assignment failed. Check logs for details.');
        }
    }

    protected $smsService;

    public function __construct(PhilSMSService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    public function due_checker(){
        $today = Carbon::today()->toDateString();
        $tasks = Task::where('status', 'Ongoing')->get();

        foreach ($tasks as $task) {
            if ($task->due < $today) {
                $task->update(['status' => 'Overdue']);

                if ($task->type === 'Group') {
                    $groupMembers = Task_group::where('task_id', $task->id)->pluck('user_id');
                    foreach ($groupMembers as $userId) {
                        $this->sendOverdueNotification($userId, $task, 'Task Group');
                    }
                } elseif ($task->type === 'Solo') {
                    $solo = Task_solo::where('task_id', $task->id)->first();
                    if ($solo) {
                        $this->sendOverdueNotification($solo->user_id, $task, 'Task Solo');
                    }
                }
            } else {
                $this->info('Task no overdue.');
            }
        }
    }

    /**
     * Send overdue notification and log the action.
     */
    private function sendOverdueNotification($userId, $task, $taskType){
        Notification::create([
            'user_id' => $userId,
            'message' => "Your Task: {$task->title} is overdue now",
            'type'    => 'warning',
        ]);

        $profile = User::find($userId);
        Log::create([
            'name'        => 'Automation',
            'action'      => "$taskType is set as overdue Task: {$task->title} User: {$profile->name}",
            'description' => date('Y-m-d'),
        ]);

        $taskUrl = '';
        if ($profile->role == 'observer') {
            $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
        } elseif ($profile->role == 'employee') {
            $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
        } elseif ($profile->role == 'intern') {
            $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
        }

        $subject = "Task {$task->title} Overdue";
        $body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Task Overdue Notification | Tribo Corporation</title>
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
                    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
                    padding: 32px 20px;
                    text-align: center;
                    border-bottom: 4px solid #A93226;
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
                    background: #fff5f5;
                    border-radius: 10px;
                    padding: 25px;
                    margin: 25px 0;
                    border-left: 4px solid #E74C3C;
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
                    background: #E74C3C;
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
                    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
                    color: #fff !important;
                    text-decoration: none;
                    padding: 14px 32px;
                    border-radius: 6px;
                    font-weight: 500;
                    margin: 25px 0;
                    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
                    transition: all 0.3s ease;
                }
                .cta-button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
                }
                .footer {
                    background: #E74C3C;
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
                .status-overdue {
                    color: #E74C3C;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='email-header'>
                    <h1>TASK OVERDUE NOTIFICATION</h1>
                </div>

                <div class='email-content'>
                    <p class='greeting'>Dear {$profile->name},</p>

                    <p>This task has passed its due date and requires your <strong>immediate attention</strong>:</p>

                    <div class='task-card'>
                        <div class='task-row'>
                            <div class='task-label'>Task Title:</div>
                            <div class='task-value'>{$task->title}</div>
                        </div>
                        <div class='task-row'>
                            <div class='task-label'>Due Date:</div>
                            <div class='task-value'>
                                {$task->due}
                                <span class='priority-indicator'>Overdue</span>
                            </div>
                        </div>
                        <div class='task-row'>
                            <div class='task-label'>Assigned By:</div>
                            <div class='task-value'>{$task->assigned_by}</div>
                        </div>
                        <div class='task-row'>
                            <div class='task-label'>Status:</div>
                            <div class='task-value'><span class='status-overdue'>{$task->status}</span></div>
                        </div>
                    </div>

                    <div class='urgency-note'>
                        <strong>⚠️ Urgent Action Required:</strong> This overdue task may be impacting other team members and project timelines. Please complete it immediately or provide an update.
                    </div>

                    <center>
                        <a href='{$taskUrl}' class='cta-button'>UPDATE TASK STATUS</a>
                    </center>

                    <p>If you've already completed this task, please update its status in the system. For any questions, contact <strong>{$task->assigned_by}</strong> directly.</p>

                    <div class='company-info'>
                        <strong>Tribo Corporation</strong><br>
                        <span style='color: #E74C3C;'>✉️</span> tribo.corp@tribo.uno<br>
                        <span style='color: #E74C3C;'>🕒</span> Support Hours: Mon-Fri, 9AM-6PM
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
        $mailer->sendMail($profile->email, $profile->name, $subject, $body);

        $smsService = new PhilSMSService();
        $message = "🚨 TASK OVERDUE: {$task->title}\n"
         . "Due: {$task->due} (PAST DUE)\n"
         . "Assigned by: {$task->assigned_by}\n"
         . "Status: {$task->status}\n"
         . "Update immediately: {$taskUrl}\n"
         . "-Tribo Corp";
        $smsService->sendSMS($profile->phone, $message);

        $this->info("Task overdue. Name: {$task->title}");
    }

    public function group_task(){
        $groupAuto = Task_group_auto::all();
        foreach($groupAuto as $group){

            $timezone = 'Asia/Manila';
            $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
            $currentTime = Carbon::now($timezone)->format('H:i:s');

            if ($group->type === 'day') {
                // Check if there is already a task assigned today
                $existingTask = Task::where('id', $group->user_id)
                    ->whereDate('assigned', Carbon::today($timezone))
                    ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                    ->first();

                if ($existingTask) {
                    $this->info("Task already assigned for today for Group ID: {$group->id}");
                    continue;
                }
            } elseif ($group->type === 'week') {
                // Check if there is already a task assigned for this week
                $existingTask = Task::where('id', $group->user_id)
                    ->whereBetween('assigned', [
                        Carbon::now($timezone)->startOfWeek()->toDateString(),
                        Carbon::now($timezone)->endOfWeek()->toDateString()
                    ])
                    ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                    ->first();

                if ($existingTask) {
                    $this->info("Task already assigned for this week for Group ID: {$group->id}");
                    continue;
                }
            }

            $daysMap = [
                "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
                "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
                "sunday"    => 7
            ];
            $currentDayNum = $daysMap[$currentDay];

            $rules = Task_group_auto::where('id', $group->id)
            ->where(function ($query) use ($currentDayNum) {
                $query->whereRaw("
                    (
                        -- Case when start_day is before or equal to end_day (normal week range)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        <=
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND ? BETWEEN
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                    )
                    OR
                    (
                        -- Case when the range wraps around the week (e.g., Friday to Monday)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        >
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND (
                            ? >=
                            (
                                CASE LOWER(start_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                            OR
                            ? <=
                            (
                                CASE LOWER(end_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                        )
                    )
                ", [$currentDayNum, $currentDayNum, $currentDayNum]);
            })
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->get();

            foreach ($rules as $rule) {

                $today = Carbon::now();
                $dayValue = $group->due; // The day value (1-31)
                // Ensure it's a valid date
                $dueDate = $today->addDays($dayValue)->toDateString();

                $existMembers = Task_group_member_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->get();
                $canAssignGroup = true; // Assume the group can be assigned initially

                foreach ($existMembers as $eMember) {
                    $existGroups = Task_group::where('user_id', $eMember->user_id)->get();

                    foreach ($existGroups as $existGroup) {
                        $existTask = Task::where('id', $existGroup->task_id)
                            ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                            ->exists();

                        if ($existTask) {
                            $canAssignGroup = false;
                            $this->info("Task Group ID: {$rule->id} has member:{$eMember->user_id} already assigned in another task group: Task Name: {$existTask->title}");
                            break 2; // Exit both inner loops
                        }
                    }
                }

                // If any member is already assigned, skip the group assignment
                if (!$canAssignGroup) {
                    continue;
                }

                $template = Task_templates::find($rule->template_id);
                if (!$template) {
                    $this->info("Template is not existing in this group automation");
                    continue;
                }

                // Fetch the fields related to the template
                $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
                $missing_messages = [];

                foreach ($fieldsCheck as $fieldCheck) {
                    $missing = [];
                    if (is_null($fieldCheck->field_name)) $missing[] = 'Field Name';
                    if (is_null($fieldCheck->options)) $missing[] = 'Options';
                    if (is_null($fieldCheck->field_label)) $missing[] = 'Field Label';

                    if (!empty($missing)) {
                        $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
                    }
                }
                if (!empty($missing_messages)) {
                    $this->info("There's not completed field in this template");
                    continue;
                }

                $profileNames = [];
                $groupProfile = Task_group_member_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->with('user') // Eager load the user relationship (if defined)
                ->get();
                $groupProfileDept = Task_group_member_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)// Eager load the user relationship (if defined)
                ->first();
                $dept_id = Member::where('user_id', $groupProfileDept->user_id)->first();
                $dept = $dept_id->department_id;

                foreach($groupProfile as $grouped){
                    $profile = $grouped->user; // Access the eager-loaded user relationship
                    if ($profile) {
                        $profileNames[] = $profile->name;
                    }
                }

                $adder = User::find($group->adder_id);
                $task = new Task();
                $task->title = $template->title;
                $task->department_id = $dept;
                $task->template_id = $rule->template_id;
                $task->assigned = date('Y-m-d');
                $task->assigned_to = !empty($profileNames) ? implode(", ", $profileNames) : 'No users assigned';
                $task->assigned_by = $adder->name;
                $task->due = $dueDate;
                $task->type = $template->type;
                $task->status = 'Ongoing';
                $task->pages = $template->pages;
                $task->progress_percentage = 0.00;
                $task->save();

                $new_task_id = $task->id;

                $groupMembers = Task_group_member_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->get();
                $this->info("{$rule->id} {$rule->template_id}");

                foreach($groupMembers as $grouped){
                    $new_group = new Task_group();
                    $new_group->task_id = $new_task_id;
                    $new_group->user_id = $grouped->user_id;
                    $new_group->save();
                }

                $pages = Task_templates_pages::where('template_id', $rule->template_id)->get();

                $page_id_map = [];
                foreach ($pages as $page) {
                    $new_pages = new Task_pages();
                    $new_pages->task_id = $new_task_id;
                    $new_pages->page_title = $page->page_title;
                    $new_pages->page_content = $page->page_content;
                    $new_pages->save();

                    $page_id_map[$page->id] = $new_pages->id;
                }

                $fields = Task_templates_fields::where('template_id', $rule->template_id)->get();

                foreach ($fields as $field) {
                    $new_fields = new Task_fields();
                    $new_fields->task_id = $new_task_id;
                    $new_fields->field_name = $field->field_name;
                    $new_fields->field_type = $field->field_type;
                    $new_fields->is_required = $field->is_required;
                    $new_fields->options = $field->options;
                    $new_fields->field_page = $page_id_map[$field->field_page] ?? null;
                    $new_fields->field_label = $field->field_label;
                    $new_fields->field_description = $field->field_description;
                    $new_fields->field_pre_answer = $field->field_pre_answer;
                    $new_fields->save();
                }



                foreach($groupMembers as $gnotif) {
                    $notif = new Notification();
                    $notif->user_id = $gnotif->user_id;
                    $notif->message = 'New Assigned Group Task';
                    $notif->type = 'info';
                    $notif->save();

                    $taskUrl = '';
                    if ($profile->role == 'observer') {
                        $taskUrl = 'https://tribo.uno/observer/etasks/'.$task->id;
                    } elseif ($profile->role == 'employee') {
                        $taskUrl = 'https://tribo.uno/employee/etasks/'.$task->id;
                    } elseif ($profile->role == 'intern') {
                        $taskUrl = 'https://tribo.uno/intern/etasks/'.$task->id;
                    }

                    $profile = User::find($gnotif->user_id);
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
                    $smsService->sendSMS($profile->phone, $message);
                }

                $setTask = Task_group_auto::where('id', $group->id)->first();
                $setTask->user_id = $new_task_id;
                $setTask->save();

                $log = new Log();
                $log->name = 'Automation';
                $log->action = "Assign Group Task: {$template->title} User: ".implode(", ", $profileNames);
                $log->description = date('Y-m-d');
                $log->save();
                $this->info("Task  Group ID: {$group->id} Complete");
            }
        }
    }

    public function solo_task(){
        $soloAuto = Task_solo_auto::all();
        foreach($soloAuto as $solo){
            $user = User::where('id', $solo->user_id)->first();
            if (!$user) continue;

            $timezone = $user->timezone ?? 'Asia/Manila';
            $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
            $currentTime = Carbon::now($timezone)->format('H:i:s');

            $existingTask = null;
            if ($solo->type === 'day') {
                $existingTask = Task::where('template_id', $solo->template_id)
                    ->whereDate('assigned', Carbon::today($timezone))
                    ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                    ->pluck('id');
            } elseif ($solo->type === 'week') {
                $existingTask = Task::where('template_id', $solo->template_id)
                    ->whereBetween('assigned', [
                        Carbon::now($timezone)->startOfWeek(),
                        Carbon::now($timezone)->endOfWeek()
                    ])
                    ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                    ->pluck('id');
            }

            // Check if user already has an existing task for the found tasks
            if ($existingTask && $existingTask->isNotEmpty()) {
                $existingAssignment = Task_solo::whereIn('task_id', $existingTask)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($existingAssignment) {
                    $this->info("Task already assigned for {$solo->type} for Solo ID: {$solo->id}");
                    continue; // Skip to next solo task if existing assignment found
                }
            }

            $daysMap = [
                "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
                "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
                "sunday"    => 7
            ];
            $currentDayNum = $daysMap[$currentDay];

            $rules = Task_solo_auto::where('id', $solo->id)
                ->where(function ($query) use ($currentDayNum) {
                $query->whereRaw("
                    (
                        -- Case when start_day is before or equal to end_day (normal week range)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        <=
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND ? BETWEEN
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                    )
                    OR
                    (
                        -- Case when the range wraps around the week (e.g., Friday to Monday)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        >
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND (
                            ? >=
                            (
                                CASE LOWER(start_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                            OR
                            ? <=
                            (
                                CASE LOWER(end_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                        )
                    )
                ", [$currentDayNum, $currentDayNum, $currentDayNum]);
            })
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->get();
            foreach ($rules as $rule) {

                $today = Carbon::now();
                $dayValue = $rule->due; // The day value (1-31)
                // Ensure it's a valid date
                $dueDate = $today->addDays($dayValue)->toDateString();

                $dept_id = Member::where('user_id', $rule->user_id)->first();
                $dept = $dept_id->department_id;
                $temp = $rule->template_id;
                $user = $rule->user_id;

                $existSolo = Task_solo::where('user_id', $user)->get();
                $hasActiveTask = false; // Flag to track if any active task exists

                if ($existSolo->isNotEmpty()) {
                    foreach ($existSolo as $solos) {
                        $task_id = $solos->task_id;
                        $existTask = Task::where('id', $task_id)
                            ->whereIn('status', ['Ongoing', 'Overdue', 'To Check'])
                            ->exists();

                        if ($existTask) {
                            $hasActiveTask = true;
                            break; // Exit early if an active task is found
                        }
                    }
                }

                if ($hasActiveTask) {
                    $this->info("User has an active task (Solo ID: {$solo->id})");
                    continue; // Skip to the next solo task
                }

                $template = Task_templates::find($temp);
                if (!$template) {
                    $this->info("This template does not exist");
                    continue;
                }

                // Fetch the fields related to the template
                $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
                $missing_messages = [];

                foreach ($fieldsCheck as $fieldCheck) {
                    $missing = [];
                    if (is_null($fieldCheck->field_name)) $missing[] = 'Field Name';
                    if (is_null($fieldCheck->options)) $missing[] = 'Options';
                    if (is_null($fieldCheck->field_label)) $missing[] = 'Field Label';

                    if (!empty($missing)) {
                        $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
                    }
                }

                if (!empty($missing_messages)) {
                    $this->info("There's not complete field in this template");
                    continue;
                }

                $adder = User::find($rule->adder_id);
                $profile = User::find($user);
                $task = new Task();
                $task->title = $template->title;
                $task->department_id = $dept;
                $task->template_id = $temp;
                $task->assigned = date('Y-m-d');
                $task->assigned_to = $profile->name;
                $task->assigned_by = $adder->name;
                $task->due = $dueDate;
                $task->type = $template->type;
                $task->status = 'Ongoing';
                $task->pages = $template->pages;
                $task->progress_percentage = 0.00;
                $task->save();

                $new_task_id = $task->id;

                $tSolo = new Task_solo();
                $tSolo->task_id = $new_task_id;
                $tSolo->user_id = $user;
                $tSolo->save();

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
                    $new_fields = new Task_fields();
                    $new_fields->task_id = $new_task_id;
                    $new_fields->field_name = $field->field_name;
                    $new_fields->field_type = $field->field_type;
                    $new_fields->is_required = $field->is_required;
                    $new_fields->options = $field->options;
                    $new_fields->field_page = $page_id_map[$field->field_page] ?? null;
                    $new_fields->field_label = $field->field_label;
                    $new_fields->field_description = $field->field_description;
                    $new_fields->field_pre_answer = $field->field_pre_answer;
                    $new_fields->save();
                }

                $notif = new Notification();
                $notif->user_id = $user;
                $notif->message = 'New Assigned Solo Task';
                $notif->type = 'info';
                $notif->save();

                $taskUrl = '';
                if ($profile->role == 'observer') {
                    $taskUrl = 'https://tribo.uno/observer/lvtasks/'.$task->id;
                } elseif ($profile->role == 'employee') {
                    $taskUrl = 'https://tribo.uno/employee/lvtasks/'.$task->id;
                } elseif ($profile->role == 'intern') {
                    $taskUrl = 'https://tribo.uno/intern/lvtasks/'.$task->id;
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
                $smsService->sendSMS($profile->phone, $message);

                $log = new Log();
                $log->name = 'Automation';
                $log->action = "Assign Solo Task: {$template->title} User: {$profile->name}";
                $log->description = date('Y-m-d');
                $log->save();
                $this->info("Task Solo ID: {$solo->id} Successfully assigned");
            }

        }
    }

    public function checker_task(){
        $checker = Task_check_auto::all();

        if($checker){
            foreach($checker as $row){
                $tasks = Task::where('template_id', $row->template_id)
                ->where('department_id', $row->department_id)
                ->where('status', 'To Check')->get();

                foreach($tasks as $task){
                    $fields = Task_fields::where('task_id', $task->id)
                        ->get();

                    if ($fields->isEmpty()) {
                        continue;
                    }

                    $allMatched = true;

                    foreach ($fields as $field) {
                        $inputValue = Task_inputs::where('field_id', $field->id)->value('value'); // Get only input value

                        if (!empty($field->field_pre_answer)) {
                            if(in_array($field->field_type, ['Radio', 'Checkbox', 'Dropdown'])){
                                $fieldPreAnswer = json_decode($field->field_pre_answer, true); // Convert JSON to array

                                if (json_last_error() !== JSON_ERROR_NONE || !$fieldPreAnswer) {
                                    $allMatched = false;
                                    break;
                                }

                                $fieldId = array_key_first($fieldPreAnswer); // Get the first key (e.g., "9", "16")
                                $expectedAnswerKey = $fieldPreAnswer[$fieldId]; // Expected key (e.g., "options_1")

                                // Handle different input types
                                if (str_starts_with($expectedAnswerKey, 'options_')) {
                                    // Radio or Dropdown Validation
                                    $optionIndex = explode('_', $expectedAnswerKey)[1] - 1; // Convert "options_1" to index 0
                                    $options = json_decode($field->options, true)[$fieldId]['options'] ?? [];

                                    if (!isset($options[$optionIndex]) || $inputValue !== $options[$optionIndex]) {
                                        $allMatched = false;
                                        break;
                                    }

                                } elseif (str_starts_with($expectedAnswerKey, 'check_label_')) {
                                    // Checkbox Validation (Checkbox is "on" if selected)
                                    if ($inputValue !== 'on') {
                                        $allMatched = false;
                                        break;
                                    }
                                }
                            } else {
                                if ($inputValue !== $field->field_pre_answer) {
                                    $allMatched = false;
                                    break;
                                }
                            }
                        } else if ($field->is_required == 1) {
                            if (empty($inputValue)) {
                                $allMatched = false;
                                break;
                            }
                        }
                    }

                    // If all fields matched, update task status to "Complete"
                    if ($allMatched) {
                        $approver = User::find($row->adder_id);
                        $task->status = 'Completed';
                        $task->approved_by = $approver->name;
                        $task->save();



                        if($task->type == "Solo"){
                            $solo = Task_solo::where('task_id', $task->id)->first();
                            $tasker = User::find($solo->user_id);
                            $notif = new Notification();
                            $notif->user_id = $tasker->id;
                            $notif->message = "Your solo task is approved by: {$approver->name} task name: {$task->title}";
                            $notif->type = 'success';
                            $notif->save();

                            $data = Task_submit_data::where('task_id', $task->id)->where('user_id', $tasker->id)->first();
                            $data->task_id = $task->id;
                            $data->department_id = $row->department_id;
                            $data->status = ($task->status == 'Overdue' ? 'Overdue' : 'Completed');
                            $data->save();

                            $log = new Log();
                            $log->name = 'Automation';
                            $log->action = "Approved Task: {$task->title}";
                            $log->description = date('Y-m-d');
                            $log->save();

                            $profile = $tasker;

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
                                                <div class='detail-value'>{$approver->name}</div>
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
                            . "Approved: ".date('M j')." by {$approver->name}\n"
                            . "Was due: {$task->due}\n"
                            . "Details: {$taskUrl}\n"
                            . "Thank you for your work!";
                            $smsService->sendSMS($profile->phone, $message);
                        } else if($task->type == "Group"){
                            $group = Task_group::where('task_id', $task->id)->get();
                            foreach($group as $member){
                                $tasker = User::find($member->user_id);
                                $notif = new Notification();
                                $notif->user_id = $tasker->id;
                                $notif->message = "Your group task is approved by: {$approver->name} task name: {$task->title}";
                                $notif->type = 'success';
                                $notif->save();

                                $data = Task_submit_data::where('task_id', $task->id)->where('user_id', $tasker->id)->first();
                                $data->task_id = $task->id;
                                $data->department_id = $row->department_id;
                                $data->status = ($task->status == 'Overdue' ? 'Overdue' : 'Completed');
                                $data->save();

                                $log = new Log();
                                $log->name = 'Automation';
                                $log->action = "Approved Task: {$task->title}";
                                $log->description = date('Y-m-d');
                                $log->save();



                                $profile = $tasker;

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

                                            <p>Dear {$task->assigned_by},</p>

                                            <p>We're pleased to inform you that the following task has been successfully completed:</p>

                                            <div class='task-details'>
                                                <div class='detail-row'>
                                                    <div class='detail-label'>Task Title:</div>
                                                    <div class='detail-value'>{$task->title}</div>
                                                </div>
                                                <div class='detail-row'>
                                                    <div class='detail-label'>Approved By:</div>
                                                    <div class='detail-value'>{$approver->name}</div>
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
                                . "Approved: ".date('M j')." by {$approver->name}\n"
                                . "Was due: {$task->due}\n"
                                . "Details: {$taskUrl}\n"
                                . "Thank you for your work!";
                                $smsService->sendSMS($profile->phone, $message);
                            }
                        }
                        $this->info("Task Match Pre Answer: {$task->title}");
                    } else {
                        $this->info("Task Not Match Pre Answer: {$task->title}");
                    }

                }

            }
        }
    }

    public function distribute_task(){
        $distributes = Task_template_distribute_auto::all();

        if($distributes->isNotEmpty()){
            foreach($distributes as $dist){
                $tasks = Task::where('template_id', $dist->template_id)
                ->where('department_id', $dist->department_id)
                ->where('status', 'Completed')
                ->get();
                if($tasks->isNotEmpty()){
                    $existingTaskIds = Task_distribute_auto::whereIn('task_id', $tasks->pluck('id'))->pluck('task_id')->toArray();

                    foreach ($tasks as $task) {

                        if (in_array($task->id, $existingTaskIds)) {
                            $this->info("Task already in automation distribution: {$task->title}");
                            continue;
                        }

                        $data[] = [
                            'template_id' => $dist->template_id,
                            'department_id' => $dist->department_id,
                            'to_department_id' => $dist->to_department_id,
                            'task_id' => $task->id,
                            'adder_id' => $dist->adder_id,
                            'title' => $task->title,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        $this->info("Task Name: {$task->title} inserted");
                    }

                    if (!empty($data)) {
                        Task_distribute_auto::insert($data);

                        $log = new Log();
                        $log->name = 'Automation';
                        $log->action = "Distribute task to : {$dist->to_department_name} id: {$dist->to_department_id} task title: {$dist->title}";
                        $log->description = date('Y-m-d');
                        $log->save();
                    }
                }
            }
        }
    }

    public function accept_distribute_task(){
        $accepts = Task_distribute_auto_accept::all();

        if($accepts->isNotEmpty()){
            $this->error("Working Accept Automation");
            foreach($accepts as $accept){
                $distributes = Task_distribute_auto::where('to_department_id', $accept->my_department_id)
                ->where('template_id', $accept->template_id)
                ->where('department_id', $accept->department_id)
                ->where('status', 'Pending')
                ->get();
                foreach($distributes as $distribute){
                    $distribute->status = 'Accepted';
                    $distribute->save();

                    $getTask = Task::where('id', $distribute->task_id)->first();
                    if (!$getTask) {
                        $this->error("Task not found for task_id: " . $distribute->task_id);
                        continue; // Skip this iteration if task doesn't exist
                    }


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
                    $log->name = 'Automation';
                    $log->action = "Distribute Accept Task: {$task->title} From: Department ID {$task->department_id} To: Department ID {$distribute->to_department_id}";
                    $log->description = date('Y-m-d');
                    $log->save();


                }
            }
        }
    }

    public function link_group_task(){
        $groupAuto = Link_group_auto::all();
        foreach($groupAuto as $group){

            $timezone = 'Asia/Manila';
            $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
            $currentTime = Carbon::now($timezone)->format('H:i:s');

            if ($group->type === 'day') {
                // Check if there is already a task assigned today
                $existingTask = Task::where('id', $group->user_id)
                    ->whereDate('assigned', Carbon::today($timezone))
                    ->whereIn('status', ['Ongoing', 'Overdue'])
                    ->whereNotNull('link_id')
                    ->exists();

                if ($existingTask) {
                    $this->info("Task already assigned for today for Group ID: {$group->id}");
                    continue;
                }
            } elseif ($group->type === 'week') {
                // Check if there is already a task assigned for this week
                $existingTask = Task::where('id', $group->user_id)
                    ->whereBetween('assigned', [
                        Carbon::now($timezone)->startOfWeek()->toDateString(),
                        Carbon::now($timezone)->endOfWeek()->toDateString()
                    ])
                    ->whereIn('status', ['Ongoing', 'Overdue'])
                    ->whereNotNull('link_id')
                    ->exists();

                if ($existingTask) {
                    $this->info("Task already assigned for this week for Group ID: {$group->id}");
                    continue;
                }
            }

            $daysMap = [
                "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
                "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
                "sunday"    => 7
            ];
            $currentDayNum = $daysMap[$currentDay];

            $validTime = Link_group_auto::where('id', $group->id)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->exists();

            if (!$validTime) {
                $this->info("❌ User ID {$solo->user_id} failed the TIME check (Current Time: {$currentTime}).");
            }


            $rules = Link_group_auto::where('id', $group->id)
            ->where(function ($query) use ($currentDayNum) {
                $query->whereRaw("
                    (
                        -- Case when start_day is before or equal to end_day (normal week range)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        <=
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND ? BETWEEN
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                    )
                    OR
                    (
                        -- Case when the range wraps around the week (e.g., Friday to Monday)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        >
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND (
                            ? >=
                            (
                                CASE LOWER(start_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                            OR
                            ? <=
                            (
                                CASE LOWER(end_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                        )
                    )
                ", [$currentDayNum, $currentDayNum, $currentDayNum]);
            })
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->get();

            if ($rules->isEmpty()) {
                $this->info("❌ Group ID {$group->id} did NOT pass the rule check.");
            } else {
                $this->info("✅ Group ID {$group->id} PASSED the rule check.");
            }

            foreach ($rules as $rule) {

                $today = Carbon::now();
                $dayValue = $group->due; // The day value (1-31)
                // Ensure it's a valid date
                $dueDate = $today->addDays($dayValue)->toDateString();

                $existMembers = Link_member_group_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->get();
                $canAssignGroup = true; // Assume the group can be assigned initially

                $conflictingTasks = []; // Store conflicting tasks to log once

                foreach ($existMembers as $eMember) {
                    $existGroups = Task_group::where('user_id', $eMember->user_id)->pluck('task_id');

                    $existTasks = Task::whereIn('id', $existGroups)
                        ->where(function ($query) {
                            $query->where('status', 'Ongoing')
                                ->orWhere('status', 'Overdue');
                        })
                        ->get();

                    if ($existTasks->isNotEmpty()) {
                        $canAssignGroup = false;
                        foreach ($existTasks as $existTask) {
                            $conflictingTasks[$existTask->id] = [
                                'user_id'   => $eMember->user_id,
                                'task_id'   => $existTask->id,
                                'title'     => $existTask->title,
                                'status'    => $existTask->status,
                                'linked_id' => $existTask->link_id
                            ];
                        }
                    }
                }

                // Log only once if there are conflicting tasks
                if (!$canAssignGroup && !empty($conflictingTasks)) {
                    foreach ($conflictingTasks as $task) {
                        $this->info("🚨 User {$task['user_id']} is already holding Task ID: {$task['task_id']} | Title: {$task['title']} | Status: {$task['status']} | Linked ID: {$task['linked_id']}");
                    }
                    continue;
                }

                $template = Task_templates::find($rule->template_id);
                if (!$template) {
                    $this->info("Template is not existing in this group automation");
                    continue;
                }

                // Fetch the fields related to the template
                $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
                $missing_messages = [];

                foreach ($fieldsCheck as $fieldCheck) {
                    $missing = [];
                    if (is_null($fieldCheck->field_name)) $missing[] = 'Field Name';
                    if (is_null($fieldCheck->options)) $missing[] = 'Options';
                    if (is_null($fieldCheck->field_label)) $missing[] = 'Field Label';

                    if (!empty($missing)) {
                        $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
                    }
                }
                if (!empty($missing_messages)) {
                    $this->info("There's not completed field in this template");
                    continue;
                }

                $profileNames = [];
                $groupProfile = Link_member_group_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->with('user') // Eager load the user relationship (if defined)
                ->get();
                $groupProfileDept = Link_member_group_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)// Eager load the user relationship (if defined)
                ->first();
                $dept_id = Member::where('user_id', $groupProfileDept->user_id)->first();
                $dept = $dept_id->department_id;

                foreach($groupProfile as $grouped){
                    $profile = $grouped->user; // Access the eager-loaded user relationship
                    if ($profile) {
                        $profileNames[] = $profile->name;
                    }
                }

                $distribute = Task::where('status', 'Distributed')
                ->where('template_id', $rule->link_id)
                ->where('department_id', $rule->department_id)
                ->latest()
                ->lockForUpdate()
                ->first();

                if(is_null($distribute)){
                    $this->info("There's no existing distributed task right now");
                    continue;
                }

                $distribute->status = 'Linked';
                $distribute->save();

                $adder = User::find($group->adder_id);
                $task = new Task();
                $task->title = $template->title;
                $task->department_id = $dept;
                $task->template_id = $rule->template_id;
                $task->link_id = $distribute->id;
                $task->assigned = date('Y-m-d');
                $task->assigned_to = !empty($profileNames) ? implode(", ", $profileNames) : 'No users assigned';
                $task->assigned_by = $adder->name;
                $task->due = $dueDate;
                $task->type = $template->type;
                $task->status = 'Ongoing';
                $task->pages = $template->pages;
                $task->progress_percentage = 0.00;
                $task->save();

                $new_task_id = $task->id;

                $groupMembers = Link_member_group_auto::where('template_id', $rule->template_id)
                ->where('group_id', $rule->id)
                ->get();
                $this->info("{$rule->id} {$rule->template_id}");

                foreach($groupMembers as $grouped){
                    $new_group = new Task_group();
                    $new_group->task_id = $new_task_id;
                    $new_group->user_id = $grouped->user_id;
                    $new_group->save();
                }

                $pages = Task_templates_pages::where('template_id', $rule->template_id)->get();

                $page_id_map = [];
                foreach ($pages as $page) {
                    $new_pages = new Task_pages();
                    $new_pages->task_id = $new_task_id;
                    $new_pages->page_title = $page->page_title;
                    $new_pages->page_content = $page->page_content;
                    $new_pages->save();

                    $page_id_map[$page->id] = $new_pages->id;
                }

                $fields = Task_templates_fields::where('template_id', $rule->template_id)->get();

                foreach ($fields as $field) {
                    $new_fields = new Task_fields();
                    $new_fields->task_id = $new_task_id;
                    $new_fields->field_name = $field->field_name;
                    $new_fields->field_type = $field->field_type;
                    $new_fields->is_required = $field->is_required;
                    $new_fields->options = $field->options;
                    $new_fields->field_page = $page_id_map[$field->field_page] ?? null;
                    $new_fields->field_label = $field->field_label;
                    $new_fields->field_description = $field->field_description;
                    $new_fields->field_pre_answer = $field->field_pre_answer;
                    $new_fields->save();
                }

                foreach($groupMembers as $gnotif) {
                    $notif = new Notification();
                    $notif->user_id = $gnotif->user_id;
                    $notif->message = 'New Assigned Group Task With Linked Task';
                    $notif->type = 'info';
                    $notif->save();
                }

                $setTask = Link_group_auto::where('id', $group->id)->first();
                $setTask->user_id = $new_task_id;
                $setTask->save();

                $log = new Log();
                $log->name = 'Automation';
                $log->action = "Assign Group Task With Link: {$template->title} User: ".implode(", ", $profileNames)."Linked Task: {$distribute->title} ID: {$distribute->id}";
                $log->description = date('Y-m-d');
                $log->save();
                $this->info("Task  Group ID: {$group->id} Complete");
            }
        }
    }

    public function link_solo_task(){
        $soloAuto = Link_solo_auto::all();
        foreach($soloAuto as $solo){
            $user = User::where('id', $solo->user_id)->first();
            if (!$user) continue;


            $timezone = 'Asia/Manila';
            $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
            $currentTime = Carbon::now($timezone)->format('H:i:s');

            if ($solo->type === 'day') {
                // Check if there is already a task assigned today
                $existingTask = Task::where('template_id', $solo->template_id)
                    ->whereDate('assigned', Carbon::today($timezone))
                    ->whereIn('status', ['Ongoing', 'Overdue'])
                    ->whereNotNull('link_id')
                    ->get();
                foreach($existingTask as $task){
                    $existingUsersDay = Task_solo::where('task_id', $task->id)->where('user_id', $user->id)->first();
                    if ($existingUsersDay) {
                        $this->info("Task already assigned for today for Solo ID: {$solo->id}");
                        continue;
                    }
                }

            } elseif ($solo->type === 'week') {
                // Check if there is already a task assigned for this week
                $existingTask = Task::where('template_id', $solo->template_id)
                    ->whereBetween('assigned', [
                        Carbon::now($timezone)->startOfWeek()->toDateString(),
                        Carbon::now($timezone)->endOfWeek()->toDateString()
                    ])
                    ->whereIn('status', ['Ongoing', 'Overdue'])
                    ->whereNotNull('link_id')
                    ->get();
                foreach($existingTask as $task){
                    $existingUsersWeek = Task_solo::where('task_id', $task->id)->where('user_id', $user->id)->first();
                    if ($existingUsersWeek) {
                        $this->info("Task already assigned for this week for Solo ID: {$solo->id}");
                        continue;
                    }
                }
            }

            $validTime = Link_solo_auto::where('id', $solo->id)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->exists();

            if (!$validTime) {
                $this->info("❌ User ID {$solo->user_id} failed the TIME check (Current Time: {$currentTime}).");
            }

            $daysMap = [
                "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
                "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
                "sunday"    => 7
            ];
            $currentDayNum = $daysMap[$currentDay];;
            $rules = Link_solo_auto::where('id', $solo->id)
                ->where(function ($query) use ($currentDayNum) {
                $query->whereRaw("
                    (
                        -- Case when start_day is before or equal to end_day (normal week range)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        <=
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND ? BETWEEN
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                    )
                    OR
                    (
                        -- Case when the range wraps around the week (e.g., Friday to Monday)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        >
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND (
                            ? >=
                            (
                                CASE LOWER(start_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                            OR
                            ? <=
                            (
                                CASE LOWER(end_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                        )
                    )
                ", [$currentDayNum, $currentDayNum, $currentDayNum]);
            })
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->get();

            if ($rules->isEmpty()) {
                $this->info("❌ User ID {$solo->user_id} did NOT pass the rule check.");
            } else {
                $this->info("✅ User ID {$solo->user_id} PASSED the rule check.");
            }

            foreach ($rules as $rule) {
                $today = Carbon::now();
                $dayValue = $rule->due; // The day value (1-31)
                // Ensure it's a valid date
                $dueDate = $today->addDays($dayValue)->toDateString();

                $dept_id = Member::where('user_id', $rule->user_id)->first();
                $dept = $dept_id->department_id;
                $temp = $rule->template_id;
                $user = $rule->user_id;

                $existSolo = Task_solo::where('user_id', $user)->first();
                if($existSolo){
                    $task_id = $existSolo->task_id;
                    $existTask = Task::where('id', $task_id)->where('status', 'Ongoing')->orWhere('status', 'Overdue')->whereNotNull('link_id')->get();
                    if ($existTask->isNotEmpty()) {
                        foreach ($existTask as $task) {
                            $this->info("This user is currently holding this task for Solo ID: {$solo->id} | Task ID: {$task->id} | Title: {$task->title} | Status: {$task->status} | Linked ID: {$task->link_id}");
                        }
                        continue;
                    }
                }
                $template = Task_templates::find($temp);
                if (!$template) {
                    $this->info("This template does not exist");
                    continue;
                }

                // Fetch the fields related to the template
                $fieldsCheck = Task_templates_fields::where('template_id', $template->id)->get();
                $missing_messages = [];

                foreach ($fieldsCheck as $fieldCheck) {
                    $missing = [];
                    if (is_null($fieldCheck->field_name)) $missing[] = 'Field Name';
                    if (is_null($fieldCheck->options)) $missing[] = 'Options';
                    if (is_null($fieldCheck->field_label)) $missing[] = 'Field Label';

                    if (!empty($missing)) {
                        $missing_messages[] = "Field ID {$fieldCheck->id} is missing: " . implode(', ', $missing);
                    }
                }

                if (!empty($missing_messages)) {
                    $this->info("There's not complete field in this template");
                    continue;
                }

                $distribute = Task::where('status', 'Distributed')
                ->where('template_id', $rule->link_id)
                ->where('department_id', $rule->department_id)
                ->latest()
                ->lockForUpdate()
                ->first();

                if(is_null($distribute)){
                    $this->info("There's no existing distributed task right now");
                    continue;
                }

                $distribute->status = 'Linked';
                $distribute->save();

                $adder = User::find($rule->adder_id);
                $profile = User::find($user);
                $task = new Task();
                $task->title = $template->title;
                $task->department_id = $dept;
                $task->template_id = $temp;
                $task->link_id = $distribute->id;
                $task->assigned = date('Y-m-d');
                $task->assigned_to = $profile->name;
                $task->assigned_by = $adder->name;
                $task->due = $dueDate;
                $task->type = $template->type;
                $task->status = 'Ongoing';
                $task->pages = $template->pages;
                $task->progress_percentage = 0.00;
                $task->save();

                $new_task_id = $task->id;

                $tSolo = new Task_solo();
                $tSolo->task_id = $new_task_id;
                $tSolo->user_id = $user;
                $tSolo->save();

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
                    $new_fields = new Task_fields();
                    $new_fields->task_id = $new_task_id;
                    $new_fields->field_name = $field->field_name;
                    $new_fields->field_type = $field->field_type;
                    $new_fields->is_required = $field->is_required;
                    $new_fields->options = $field->options;
                    $new_fields->field_page = $page_id_map[$field->field_page] ?? null;
                    $new_fields->field_label = $field->field_label;
                    $new_fields->field_description = $field->field_description;
                    $new_fields->field_pre_answer = $field->field_pre_answer;
                    $new_fields->save();
                }

                $notif = new Notification();
                $notif->user_id = $user;
                $notif->message = 'New Assigned Solo Task With Linked Task';
                $notif->type = 'info';
                $notif->save();

                $log = new Log();
                $log->name = 'Automation';
                $log->action = "Assign Solo Task With Link: {$template->title} User: {$profile->name} Linked: {$distribute->title} ID: {$distribute->id}";
                $log->description = date('Y-m-d');
                $log->save();
                $this->info("Task Solo ID: {$solo->id} Successfully assigned");
            }

        }
    }

    public function set_sleep(){
        $department = Department::all();
        $timezone = 'Asia/Manila';
        $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
        $currentTime = Carbon::now($timezone)->format('H:i:s');

        $daysMap = [
            "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
            "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
            "sunday"    => 7
        ];
        $currentDayNum = $daysMap[$currentDay];

        foreach($department as $dept_sleep){

            $rules = $dept_sleep->where('id', $dept_sleep->id)->whereNotNull('start_day')->whereNotNull('end_day')->whereNotNull('start_time')->whereNotNull('end_time')->get();

            if (!$rules) continue; // Skip if no rule exists for the department

            foreach($rules as $rule){
                $startDayNum = $daysMap[strtolower($rule->start_day)];
                $endDayNum = $daysMap[strtolower($rule->end_day)];
                $startTime = $rule->start_time;
                $endTime = $rule->end_time;
                $isWorkingDay = ($startDayNum <= $endDayNum && $currentDayNum >= $startDayNum && $currentDayNum <= $endDayNum)
                || ($startDayNum > $endDayNum && ($currentDayNum >= $startDayNum || $currentDayNum <= $endDayNum));

                // If today is not a working day, set tasks to Sleep
                if (!$isWorkingDay) {
                    $this->setTasksToSleep($dept_sleep->id);
                    continue; // Skip to next department
                }

                // If current time is outside working hours, set tasks to Sleep
                if ($currentTime < $startTime || $currentTime > $endTime) {
                    $this->setTasksToSleep($dept_sleep->id);
                } else {
                    // Otherwise, wake up tasks
                    $this->setTasksToAway($dept_sleep->id);
                }
            }
        }
    }

    private function setTasksToSleep($departmentId){
        $affectedTasks = Task::where('department_id', $departmentId)
            ->whereIn('user_status', ['Idle', 'Active', 'Away', 'Emergency'])
            ->whereIn('status', ['Overdue', 'Ongoing'])
            ->get(['id', 'type']);

        if ($affectedTasks->isNotEmpty()) {
            foreach ($affectedTasks as $task) {
                if ($task->type == "Group") {
                    $group = Task_group::where('task_id', $task->id)->get();
                    foreach ($group as $member) {
                        Task_user_status::create([
                            'task_id' => $task->id,
                            'user_id' => $member->user_id,
                            'user_status' => 'Sleep'
                        ]);
                    }
                } else if ($task->type == "Solo") {
                    $solo = Task_solo::where('task_id', $task->id)->first();
                    if ($solo) {
                        Task_user_status::create([
                            'task_id' => $task->id,
                            'user_id' => $solo->user_id,
                            'user_status' => 'Sleep'
                        ]);
                    }
                }
                $task->update(['user_status' => 'Sleep']);
            }
        }

        $this->info("Tasks set to Sleep for department {$departmentId}.");
    }

    private function setTasksToAway($departmentId){
        $timezone = 'Asia/Manila';
        $currentDate = Carbon::now($timezone)->toDateString();
        $affectedTasks = Task::where('department_id', $departmentId)
        ->whereIn('user_status', ['Sleep', 'Request Overtime', 'Overtime'])
        ->whereIn('status', ['Overdue', 'Ongoing'])
        ->where(function ($query) use ($currentDate) {
            $query->whereNull('last_updated_at') // If never updated
                ->orWhereDate('last_updated_at', '!=', $currentDate); // If last update was not today
        })
        ->get(['id', 'type', 'last_updated_at']);

        if ($affectedTasks->isNotEmpty()) {
            foreach ($affectedTasks as $task) {
                if ($task->type == "Group") {
                    $group = Task_group::where('task_id', $task->id)->get();
                    foreach ($group as $member) {
                        Task_user_status::create([
                            'task_id' => $task->id,
                            'user_id' => $member->user_id,
                            'user_status' => 'Away'
                        ]);
                    }
                } else if ($task->type == "Solo") {
                    $solo = Task_solo::where('task_id', $task->id)->first();
                    if ($solo) {
                        Task_user_status::create([
                            'task_id' => $task->id,
                            'user_id' => $solo->user_id,
                            'user_status' => 'Away'
                        ]);
                    }
                }
                $task->update([
                    'user_status' => 'Away',
                    'last_updated_at' => Carbon::now($timezone) // Save update timestamp
                ]);
            }
            $this->info("Tasks set to Away for department {$departmentId}.");
        }  else {
            // If all tasks were already updated today, show message
            $this->info("Tasks for department {$departmentId} are already set to Away today.");
        }


    }

    public function accept_overtime(){
        $department = Department::where('overtime_auto', 'On')->get();
        $timezone = 'Asia/Manila';
        $currentDay = strtolower(Carbon::now($timezone)->format('l')); // e.g., "monday"
        $currentTime = Carbon::now($timezone)->format('H:i:s');

        $daysMap = [
            "monday"    => 1, "tuesday"   => 2, "wednesday" => 3,
            "thursday"  => 4, "friday"    => 5, "saturday"  => 6,
            "sunday"    => 7
        ];
        $currentDayNum = $daysMap[$currentDay];

        foreach($department as $dept_sleep){

            $rules = $dept_sleep->where('id', $dept_sleep->id)
                ->where(function ($query) use ($currentDayNum) {
                $query->whereRaw("
                    (
                        -- Case when start_day is before or equal to end_day (normal week range)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        <=
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND ? BETWEEN
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                    )
                    OR
                    (
                        -- Case when the range wraps around the week (e.g., Friday to Monday)
                        (
                            CASE LOWER(start_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        >
                        (
                            CASE LOWER(end_day)
                                WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                WHEN 'sunday' THEN 7 END
                        )
                        AND (
                            ? >=
                            (
                                CASE LOWER(start_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                            OR
                            ? <=
                            (
                                CASE LOWER(end_day)
                                    WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3
                                    WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6
                                    WHEN 'sunday' THEN 7 END
                            )
                        )
                    )
                ", [$currentDayNum, $currentDayNum, $currentDayNum]);
            })
            ->get();
            if ($rules->isEmpty()) {
                continue; // Skip if no matching rules found
            }
            foreach ($rules as $rule) {
                $startTime = $rule->start_time; // 08:00:00
                $endTime = $rule->end_time; // 17:00:00

                // If current time is **before** start_time or **after** end_time → Set to Sleep
                if ($currentTime < $startTime || $currentTime > $endTime) {
                    $affectedTasks = Task::where('department_id', $rule->id)
                    ->whereIn('user_status', ['Request Overtime'])
                    ->whereIn('status', ['Overdue', 'Ongoing'])
                    ->get(['id', 'type']);

                    if ($affectedTasks->isNotEmpty()) {
                        foreach($affectedTasks as $task){
                            if($task->type == "Group"){
                                $group = Task_group::where('task_id', $task->id)->get();
                                if($group->isNotEmpty()){
                                    foreach($group as $member){
                                        $status = new Task_user_status();
                                        $status->task_id = $task->id;
                                        $status->user_id = $member->user_id;
                                        $status->user_status = 'Overtime';
                                        $status->save();

                                        $notif = new Notification();
                                        $notif->user_id = $member->user_id;
                                        $notif->message = 'Overtime Request Accepted By System';
                                        $notif->type = 'info';
                                        $notif->save();
                                    }
                                }

                                Log::create([
                                    'name' => 'Automation',
                                    'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                                    'description' => now()->format('Y-m-d'),
                                ]);

                            } else if($task->type == "Solo"){
                                $solo = Task_solo::where('task_id', $task->id)->first();
                                if($solo){
                                    $status = new Task_user_status();
                                    $status->task_id = $task->id;
                                    $status->user_id = $solo->user_id;
                                    $status->user_status = 'Overtime';
                                    $status->save();

                                    $notif = new Notification();
                                    $notif->user_id = $solo->user_id;
                                    $notif->message = 'Overtime Request Accepted By System';
                                    $notif->type = 'info';
                                    $notif->save();

                                    Log::create([
                                        'name' => 'Automation',
                                        'action' => "Set Task To Emergency: {$task->title} ID: {$task->id}",
                                        'description' => now()->format('Y-m-d'),
                                    ]);
                                }
                            }
                            $task->update(['user_status' => 'Overtime']);
                        }
                    }

                    $this->info("Tasks accept overtime {$rule->id}.");
                }
            }

        }
    }

    public function user_activity(){
        DB::table('users')
        ->where('is_online', true)
        ->where('last_active', '<', Carbon::now('Asia/Manila')->subMinutes(5)) // If inactive for 5 minutes
        ->update([
            'is_online' => false,
            'last_online' => DB::raw('last_active') // Save last online timestamp
        ]);
    }
}
