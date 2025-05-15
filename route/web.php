<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\ObserverController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/manager/dashboard'); // Redirect logged-in users
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect(\App\Providers\RouteServiceProvider::home());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth','role:admin'])->group(function() {
    Route::get('/admin/getallchats', [AdminController::class, 'AdminDashboardGetAllChats'])->name('admin.getallchats');
    Route::get('/admin/getallnotification', [AdminController::class, 'AdminDashboardGetAllNotification'])->name('admin.getallnotification');
    Route::post('/admin/markasreadednotification', [AdminController::class, 'AdminDashboardMarkAsReadedNotification'])->name('admin.markasreadednotification');
    Route::post('/admin/clearnotification', [AdminController::class, 'AdminDashboardClearNotification'])->name('admin.clearnotification');

    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');

    Route::post('/admin/tasks/reload-myongoing-div', [AdminController::class, 'reloadMyOngoingDiv'])->name('reloadad.myongoing.div');

    Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');

    Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/admin/profile/photo', [AdminController::class, 'SavePhoto'])->name('admin.profile.photo');
    Route::post('/admin/profile/pinfo', [AdminController::class, 'ProfileInfo'])->name('admin.profile.pinfo');
    Route::post('/admin/profile/binfo', [AdminController::class, 'BasicInfo'])->name('admin.profile.binfo');
    Route::post('/admin/profile/update', [AdminController::class, 'UpdatePassword'])->name('admin.profile.update');

    //region Task

    Route::get('/admin/tasks', [AdminController::class, 'AdminTasks'])->name('admin.tasks');
    Route::post('/admin/tasks/apage', [AdminController::class, 'AdminTasksAddPage'])->name('admin.tasks.apage');
    Route::get('/admin/tasks/etemp', [AdminController::class, 'AdminTasksEditTemplate'])->name('admin.tasks.etemp');
    Route::get('/admin/tasks/eitemp', [AdminController::class, 'AdminTasksEditInfoTemplate'])->name('admin.tasks.eitemp');
    Route::post('/admin/tasks/sptitle', [AdminController::class, 'AdminTasksSavePageTitle'])->name('admin.tasks.sptitle');
    Route::post('/admin/tasks/stinfo', [AdminController::class, 'AdminTasksSaveTaskInfo'])->name('admin.tasks.stinfo');
    Route::post('/admin/tasks/create', [AdminController::class, 'AdminTasksCreate'])->name('admin.tasks.create');
    Route::post('/admin/tasks/rpage', [AdminController::class, 'AdminTasksRemovePage'])->name('admin.tasks.rpage');
    Route::post('/admin/tasks/afield', [AdminController::class, 'AdminTasksAddField'])->name('admin.tasks.afield');
    Route::post('/admin/tasks/sfinput', [AdminController::class, 'AdminTasksSaveFieldInput'])->name('admin.tasks.sfinput');
    Route::get('/admin/tasks/goradio', [AdminController::class, 'AdminTasksGetOptionRadio'])->name('admin.tasks.goradio');
    Route::get('/admin/tasks/godown', [AdminController::class, 'AdminTasksGetOptionDown'])->name('admin.tasks.godown');
    Route::post('/admin/tasks/rfrow', [AdminController::class, 'AdminTasksRemoveFieldRow'])->name('admin.tasks.rfrow');
    Route::get('/admin/tasks/eftask', [AdminController::class, 'AdminTasksEditFieldTask'])->name('admin.tasks.eftask');
    Route::post('/admin/tasks/cstask', [AdminController::class, 'AdminTasksChangeStepperTask'])->name('admin.tasks.cstask');
    Route::get('/admin/tasks/vdtdept', [AdminController::class, 'AdminTasksViewDistributeTaskDepartment'])->name('admin.tasks.vdtdept');
    Route::post('/admin/tasks/sdtdept', [AdminController::class, 'AdminTasksSubmitDistributeTaskDepartment'])->name('admin.tasks.sdtdept');
    Route::get('/admin/tasks/cdtdept', [AdminController::class, 'AdminTasksCheckDistributeTaskDepartment'])->name('admin.tasks.cdtdept');
    Route::get('/admin/tasks/vlsatask', [AdminController::class, 'AdminTasksViewListSoloAssignTask'])->name('admin.tasks.vlsatask');
    Route::post('/admin/tasks/sastask', [AdminController::class, 'AdminTasksSubmitAssignSoloTask'])->name('admin.tasks.sastask');
    Route::get('/admin/tasks/vlgatask', [AdminController::class, 'AdminTasksViewListGroupAssignTask'])->name('admin.tasks.vlgatask');
    Route::post('/admin/tasks/atagtask', [AdminController::class, 'AdminTasksAddTemporaryAssignGroupTask'])->name('admin.tasks.atagtask');
    Route::post('/admin/tasks/rtagtask', [AdminController::class, 'AdminTasksRemoveTemporaryAssignGroupTask'])->name('admin.tasks.rtagtask');
    Route::post('/admin/tasks/sagtask', [AdminController::class, 'AdminTasksSubmitAssignGroupTask'])->name('admin.tasks.sagtask');
    Route::get('/admin/tasks/vlsaatask', [AdminController::class, 'AdminTasksViewListSoloAutomationAssignTask'])->name('admin.tasks.vlsaatask');
    Route::post('/admin/tasks/slsaatask', [AdminController::class, 'AdminTasksSubmitListSoloAutomationAssignTask'])->name('admin.tasks.slsaatask');
    Route::get('/admin/tasks/vlgaatask', [AdminController::class, 'AdminTasksViewListGroupAutomationAssignTask'])->name('admin.tasks.vlgaatask');
    Route::post('/admin/tasks/slgaatask', [AdminController::class, 'AdminTasksSubmitListGroupAutomationAssignTask'])->name('admin.tasks.slgaatask');
    Route::post('/admin/tasks/rtaagtask', [AdminController::class, 'AdminTasksRemoveTemporaryAutomationAssignGroupTask'])->name('admin.tasks.rtaagtask');
    Route::post('/admin/tasks/rtaastask', [AdminController::class, 'AdminTasksRemoveTemporaryAutomationAssignSoloTask'])->name('admin.tasks.rtaastask');
    Route::post('/admin/tasks/rtaagltask', [AdminController::class, 'AdminTasksRemoveTemporaryAutomationAssignGroupListTask'])->name('admin.tasks.rtaagltask');
    Route::post('/admin/tasks/slglaatask', [AdminController::class, 'AdminTasksSubmitListGroupMemberAutomationAssignTask'])->name('admin.tasks.slglaatask');
    Route::post('/admin/tasks/arctemp', [AdminController::class, 'AdminTasksArchiveTemplate'])->name('admin.tasks.arctemp');
    Route::post('/admin/tasks/reload-ongoing-div', [AdminController::class, 'reloadOngoingDiv'])->name('reload.ongoing.div');
    Route::post('/admin/tasks/reload-tocheck-div', [AdminController::class, 'reloadToCheckDiv'])->name('reloadad.tocheck.div');
    Route::post('/admin/tasks/reload-complete-div', [AdminController::class, 'reloadCompleteDiv'])->name('reloadad.complete.div');
    Route::post('/admin/tasks/reload-distribute-div', [AdminController::class, 'reloadDistributeDiv'])->name('reloadad.distribute.div');
    Route::post('/admin/tasks/reload-distributereq-div', [AdminController::class, 'reloadDistributeReqDiv'])->name('reloadad.distributereq.div');
    Route::post('/admin/tasks/reload-overtimereq-div', [AdminController::class, 'reloadOvertimeReqDiv'])->name('reloadad.overtimereq.div');

    Route::get('/admin/tasks/vtctask', [AdminController::class, 'AdminTasksViewListTaskCheckingAutomation'])->name('admin.tasks.vtctask');
    Route::post('/admin/tasks/satctask', [AdminController::class, 'AdminSubmitAutomationToCheckTask'])->name('admin.tasks.satctask');
    Route::post('/admin/tasks/ratctask', [AdminController::class, 'AdminRemoveAutomationToCheckTask'])->name('admin.tasks.ratctask');
    Route::post('/admin/tasks/approvetask', [AdminController::class, 'AdminApproveTask'])->name('admin.tasks.approvetask');
    Route::post('/admin/tasks/declinetask', [AdminController::class, 'AdminDeclineTask'])->name('admin.tasks.declinetask');
    Route::get('/admin/tasks/vdtask', [AdminController::class, 'AdminTasksDistributionAutomation'])->name('admin.tasks.vdtask');
    Route::post('/admin/tasks/sadtask', [AdminController::class, 'AdminSubmitAutomationDistributeTask'])->name('admin.tasks.sadtask');
    Route::post('/admin/tasks/radtask', [AdminController::class, 'AdminRemoveAutomationDistributeTask'])->name('admin.tasks.radtask');
    Route::post('/admin/tasks/adisttask', [AdminController::class, 'AdminAcceptDistributeTask'])->name('admin.tasks.adisttask');
    Route::post('/admin/tasks/ddisttask', [AdminController::class, 'AdminDeclineDistributeTask'])->name('admin.tasks.ddisttask');
    Route::post('/admin/tasks/disttask', [AdminController::class, 'AdminDistributeTask'])->name('admin.tasks.disttask');
    Route::post('/admin/tasks/aadisttask', [AdminController::class, 'AdminAcceptAllDistributeTask'])->name('admin.tasks.aadisttask');
    Route::post('/admin/tasks/dadisttask', [AdminController::class, 'AdminDeclineAllDistributeTask'])->name('admin.tasks.dadisttask');
    Route::get('/admin/tasks/vaadtask', [AdminController::class, 'AdminTasksAcceptDistributionAutomation'])->name('admin.tasks.vaadtask');
    Route::post('/admin/tasks/saadtask', [AdminController::class, 'AdminTasksSubmitAcceptDistributionAutomation'])->name('admin.tasks.saadtask');
    Route::post('/admin/tasks/raadtask', [AdminController::class, 'AdminTasksRemoveAcceptDistributionAutomation'])->name('admin.tasks.raadtask');
    Route::get('/admin/tasks/vnttlink', [AdminController::class, 'AdminTasksViewNewTaskToLink'])->name('admin.tasks.vnttlink');
    Route::get('/admin/tasks/checklinktemp', [AdminController::class, 'AdminTasksCheckLinkTemp'])->name('admin.tasks.checklinktemp');
    Route::post('/admin/tasks/removelinktemp', [AdminController::class, 'AdminTasksRemoveLinkTemp'])->name('admin.tasks.removelinktemp');
    Route::post('/admin/tasks/addmemberlinktemp', [AdminController::class, 'AdminTasksAddMemberLinkTemp'])->name('admin.tasks.addmemberlinktemp');
    Route::post('/admin/tasks/removememberlinktemp', [AdminController::class, 'AdminTasksRemoveMemberLinkTemp'])->name('admin.tasks.removememberlinktemp');
    Route::post('/admin/tasks/linkandassigntask', [AdminController::class, 'AdminTasksLinkAndAssignTask'])->name('admin.tasks.linkandassigntask');
    Route::post('/admin/tasks/grouplinkandassigntask', [AdminController::class, 'AdminTasksGroupLinkAndAssignTask'])->name('admin.tasks.grouplinkandassigntask');
    Route::post('/admin/tasks/linkandassignongoingtask', [AdminController::class, 'AdminTasksLinkAndAssignOngoingTask'])->name('admin.tasks.linkandassignongoingtask');
    Route::get('/admin/tasks/viewlinkauto', [AdminController::class, 'AdminTasksViewLinkAuto'])->name('admin.tasks.viewlinkauto');
    Route::post('/admin/tasks/setsololinkauto', [AdminController::class, 'AdminTasksSetSoloLinkAuto'])->name('admin.tasks.setsololinkauto');
    Route::post('/admin/tasks/removesololinkauto', [AdminController::class, 'AdminTasksRemoveSoloLinkAuto'])->name('admin.tasks.removesololinkauto');
    Route::post('/admin/tasks/addtempogrouplinkauto', [AdminController::class, 'AdminTasksAddTempoGroupLinkAuto'])->name('admin.tasks.addtempogrouplinkauto');
    Route::post('/admin/tasks/removetempogrouplinkauto', [AdminController::class, 'AdminTasksRemoveTempoGroupLinkAuto'])->name('admin.tasks.removetempogrouplinkauto');
    Route::post('/admin/tasks/groupchecktempomemberlink', [AdminController::class, 'AdminTasksGroupCheckTempoMemberLink'])->name('admin.tasks.groupchecktempomemberlink');
    Route::post('/admin/tasks/setgrouplinkauto', [AdminController::class, 'AdminTasksSetGroupLinkAuto'])->name('admin.tasks.setgrouplinkauto');
    Route::post('/admin/tasks/removegrouplinkauto', [AdminController::class, 'AdminTasksRemoveGroupLinkAuto'])->name('admin.tasks.removegrouplinkauto');
    Route::get('/admin/tasks/viewstatistic', [AdminController::class, 'AdminTasksViewStatistic'])->name('admin.tasks.viewstatistic');
    Route::post('/admin/tasks/setemergencyuserstatus', [AdminController::class, 'AdminTasksSetEmergencyUserStatus'])->name('admin.tasks.setemergencyuserstatus');
    Route::post('/admin/tasks/setovertimeautomation', [AdminController::class, 'AdminTasksSetOverTimeAutomation'])->name('admin.tasks.setovertimeautomation');
    Route::get('/admin/tasks/getworktimesettings', [AdminController::class, 'AdminTasksGetWorkTimeSettings'])->name('admin.tasks.getworktimesettings');
    Route::post('/admin/tasks/setworktimeautomation', [AdminController::class, 'AdminTasksSetWorkTimeAutomation'])->name('admin.tasks.setworktimeautomation');
    Route::post('/admin/tasks/requestovertimetask', [AdminController::class, 'AdminTasksRequestOvertimeTask'])->name('admin.tasks.requestovertimetask');
    Route::post('/admin/tasks/acceptovertimerequest', [AdminController::class, 'AdminTasksAcceptOvertimeRequest'])->name('admin.tasks.acceptovertimerequest');
    Route::post('/admin/tasks/declineovertimerequest', [AdminController::class, 'AdminTasksDeclineOvertimeRequest'])->name('admin.tasks.declineovertimerequest');
    Route::post('/admin/tasks/archivedistributedtask', [AdminController::class, 'AdminTasksArchiveDistributedTask'])->name('admin.tasks.archivedistributedtask');
    Route::post('/admin/tasks/archivecompletedtask', [AdminController::class, 'AdminTasksArchiveCompletedTask'])->name('admin.tasks.archivecompletedtask');
    Route::post('/admin/tasks/retrievetask', [AdminController::class, 'AdminTasksRetrievetask'])->name('admin.tasks.retrievetask');
    Route::post('/admin/tasks/deletetask', [AdminController::class, 'AdminTasksDeletetask'])->name('admin.tasks.deletetask');
    Route::post('/admin/tasks/retrievetemp', [AdminController::class, 'AdminTasksRetrievetemp'])->name('admin.tasks.retrievetemp');
    Route::post('/admin/tasks/deletetemp', [AdminController::class, 'AdminTasksDeletetemp'])->name('admin.tasks.deletetemp');
    Route::post('/admin/chat/sendtaskcontactmessage', [AdminController::class, 'AdminChatSendTaskContactMessage'])->name('admin.chat.sendtaskcontactmessage');

    Route::get('/admin/etasks/{task}', [AdminController::class, 'AdminEditTasks'])->name('admin.etasks');
    Route::post('/admin/etasks/save', [AdminController::class, 'AdminEditSaveTasks'])->name('admin.etasks.save');
    Route::post('/admin/etasks/userstatuschecking', [AdminController::class, 'AdminEditUserStatusCheckingTasks'])->name('admin.etasks.userstatuschecking');

    Route::get('/admin/lvtasks/{task}', [AdminController::class, 'AdminLiveViewTasks'])->name('admin.lvtasks');
    Route::get('/admin/tasks/glvtasks', [AdminController::class, 'AdminGetLiveViewTasks'])->name('admin.tasks.glvtasks');
    Route::get('/admin/tasks/getradio', [AdminController::class, 'AdminTasksGetRadio'])->name('admin.tasks.getradio');
    Route::get('/admin/tasks/getdown', [AdminController::class, 'AdminTasksGetDown'])->name('admin.tasks.getdown');
    Route::post('/admin/tasks/livereloading', [AdminController::class, 'AdminTasksLiveReloading'])->name('admin.tasks.livereloading');
    Route::post('/admin/tasks/unlinktask', [AdminController::class, 'AdminTasksUnlinkTask'])->name('admin.tasks.unlinktask');

    Route::get('/admin/ptasks/{task}', [AdminController::class, 'AdminPrintTasks'])->name('admin.ptasks');
    Route::get('/admin/tasks/gptasks', [AdminController::class, 'AdminGetPrintTasks'])->name('admin.tasks.gptasks');
    Route::get('/admin/tasks/printgetradio', [AdminController::class, 'AdminTasksPrintGetRadio'])->name('admin.tasks.printgetradio');
    Route::get('/admin/tasks/printgetdown', [AdminController::class, 'AdminTasksPrintGetDown'])->name('admin.tasks.printgetdown');
    Route::post('/admin/tasks/liveprintreloading', [AdminController::class, 'AdminTasksLivePrintReloading'])->name('admin.tasks.liveprintreloading');

//endregion

    Route::get('/admin/department', [AdminController::class, 'AdminDepartment'])->name('admin.department');
    Route::post('/admin/department/add', [AdminController::class, 'AdminAddDepartment'])->name('admin.department.add');
    Route::get('/admin/department/user', [AdminController::class, 'AdminGetUserDepartments'])->name('admin.department.user');
    Route::get('/admin/department/head', [AdminController::class, 'AdminGetDepartmentHead'])->name('admin.department.head');
    Route::get('/admin/department/member', [AdminController::class, 'AdminGetDepartmentMember'])->name('admin.department.member');
    Route::get('/admin/department/vhead', [AdminController::class, 'AdminGetDepartmentVHead'])->name('admin.department.vhead');
    Route::get('/admin/department/vmember', [AdminController::class, 'AdminGetDepartmentVMember'])->name('admin.department.vmember');
    Route::get('/admin/department/edept', [AdminController::class, 'AdminGetDepartmentEDept'])->name('admin.department.edept');
    Route::post('/admin/department/edit', [AdminController::class, 'AdminEditDepartment'])->name('admin.department.edit');
    Route::post('/admin/department/rdept', [AdminController::class, 'AdminDepartmentRDept'])->name('admin.department.rdept');
    Route::get('/admin/department/vuser', [AdminController::class, 'AdminGetDepartmentVUser'])->name('admin.department.vuser');
    Route::get('/admin/department/muser', [AdminController::class, 'AdminGetDepartmentMUser'])->name('admin.department.muser');
    Route::post('/admin/department/assign', [AdminController::class, 'AdminAssignDepartmentMember'])->name('admin.department.assign');
    Route::post('/admin/department/remove', [AdminController::class, 'AdminRemoveDepartmentMember'])->name('admin.department.remove');

    Route::get('/admin/users', [AdminController::class, 'AdminUsers'])->name('admin.users');
    Route::post('/admin/users/add', [AdminController::class, 'AdminAddUser'])->name('admin.users.add');
    Route::get('/admin/users/view', [AdminController::class, 'AdminUsersView'])->name('admin.users.view');
    Route::get('/admin/users/edit', [AdminController::class, 'AdminUsersEdit'])->name('admin.users.edit');
    Route::post('/admin/users/siedit', [AdminController::class, 'AdminUsersSIEdit'])->name('admin.users.siedit');
    Route::post('/admin/users/delete', [AdminController::class, 'AdminDeleteUser'])->name('admin.users.delete');

    //region Calendar

    Route::get('/admin/calendar', [AdminController::class, 'AdminCalendar'])->name('admin.calendar');
    Route::get('/admin/calendar/viewtaskdate', [AdminController::class, 'AdminCalendarViewTaskDate'])->name('admin.calendar.viewtaskdate');
    Route::post('/admin/calendar/saveevent', [AdminController::class, 'AdminCalendarSaveEvent'])->name('admin.calendar.saveevent');
    Route::get('/admin/calendar/viewprivateeventdate', [AdminController::class, 'AdminCalendarViewPrivateEventDate'])->name('admin.calendar.viewprivateeventdate');
    Route::get('/admin/calendar/viewdepartmenteventdate', [AdminController::class, 'AdminCalendarViewDepartmentEventDate'])->name('admin.calendar.viewdepartmenteventdate');
    Route::post('/admin/calendar/removeevent', [AdminController::class, 'AdminCalendarRemoveEvent'])->name('admin.calendar.removeevent');
    Route::get('/admin/calendar/viewannouncementeventdate', [AdminController::class, 'AdminCalendarViewAnnouncementEventDate'])->name('admin.calendar.viewannouncementeventdate');

    //endregion

    //region Chat
    Route::get('/admin/chat', [AdminController::class, 'AdminChat'])->name('admin.chat');
    Route::post('/admin/chat/sendcontactmessage', [AdminController::class, 'AdminChatSendContactMessage'])->name('admin.chat.sendcontactmessage');
    Route::post('/admin/chat/reload-chat-list', [AdminController::class, 'reloadChatList'])->name('reloadad.chat.list');
    Route::get('/admin/chat/viewchats', [AdminController::class, 'AdminViewChats'])->name('admin.chat.viewchats');
    Route::post('/admin/chat/sendmessage', [AdminController::class, 'AdminChatSendMessage'])->name('admin.chat.sendmessage');
    Route::post('/admin/chat/reload-chat-message', [AdminController::class, 'reloadChatMessage'])->name('reloadad.chat.message');
    Route::get('/admin/chat/checkmessageattachment', [AdminController::class, 'AdminCheckMessageAttachment'])->name('admin.chat.checkmessageattachment');
    Route::get('/admin/chat/replieduser', [AdminController::class, 'AdminChatRepliedUser'])->name('admin.chat.replieduser');
    Route::get('/admin/chat/checkmessagereply', [AdminController::class, 'AdmincheckMessageReply'])->name('admin.chat.checkmessagereply');
    Route::get('/admin/chat/geteditmessage', [AdminController::class, 'AdminGetEditMessage'])->name('admin.chat.geteditmessage');
    Route::post('/admin/chat/editmessage', [AdminController::class, 'AdminChatEditMessage'])->name('admin.chat.editmessage');
    Route::get('/admin/chat/vieweditmessage', [AdminController::class, 'AdminViewEditMessage'])->name('admin.chat.vieweditmessage');
    Route::get('/admin/chat/viewmessagecontact', [AdminController::class, 'AdminViewMessageContact'])->name('admin.chat.viewmessagecontact');
    Route::post('/admin/chat/sendforwardmessage', [AdminController::class, 'AdminChatSendForwardMessage'])->name('admin.chat.sendforwardmessage');
    Route::get('/admin/chat/checkwhoforward', [AdminController::class, 'AdminCheckWhoForward'])->name('admin.chat.checkwhoforward');
    Route::post('/admin/chat/unsendmessage', [AdminController::class, 'AdminChatUnsendMessage'])->name('admin.chat.unsendmessage');
    Route::post('/admin/chat/pinmessage', [AdminController::class, 'AdminChatPinMessage'])->name('admin.chat.pinmessage');
    Route::post('/admin/chat/unpinmessage', [AdminController::class, 'AdminChatUnpinMessage'])->name('admin.chat.unpinmessage');
    Route::post('/admin/chat/react', [AdminController::class, 'AdminChatReact'])->name('admin.chat.react');
    Route::post('/admin/chat/reload-chat-pinned', [AdminController::class, 'reloadChatPinned'])->name('reloadad.chat.pinned');
    Route::get('/admin/chat/viewpinnedmessage', [AdminController::class, 'AdminViewPinnedMessage'])->name('admin.chat.viewpinnedmessage');
    Route::post('/admin/chat/setnickname', [AdminController::class, 'AdminChatSetNickname'])->name('admin.chat.setnickname');
    Route::post('/admin/chat/setmutedchat', [AdminController::class, 'AdminChatSetMutedChat'])->name('admin.chat.setmutedchat');
    Route::post('/admin/chat/searchmessagevalue', [AdminController::class, 'AdminChatSearchMessageValue'])->name('admin.chat.searchmessagevalue');
    Route::get('/admin/chat/{chat}/media', [AdminController::class, 'getChatMedia'])->name('admin.chat.media');
    Route::post('/admin/chat/savemeeting', [AdminController::class, 'AdminChatSaveMeeting'])->name('admin.chat.savemeeting');
    Route::post('/admin/chat/removemeeting', [AdminController::class, 'AdminChatRemoveMeeting'])->name('admin.chat.removemeeting');
    Route::post('/admin/chat/creategroupmessage', [AdminController::class, 'AdminChatCreateGroupMessage'])->name('admin.chat.creategroupmessage');
    Route::post('/admin/chat/setconvoimage', [AdminController::class, 'AdminChatSetConvoImage'])->name('admin.chat.setconvoimage');
    Route::post('/admin/chat/unsetconvoimage', [AdminController::class, 'AdminChatUnsetConvoImage'])->name('admin.chat.unsetconvoimage');
    Route::post('/admin/chat/setconvoname', [AdminController::class, 'AdminChatSetConvoName'])->name('admin.chat.setconvoname');
    Route::post('/admin/chat/unsetconvoname', [AdminController::class, 'AdminChatUnsetConvoName'])->name('admin.chat.unsetconvoname');
    Route::post('/admin/chat/addnewmembergroup', [AdminController::class, 'AdminChatAddNewMemberGroup'])->name('admin.chat.addnewmembergroup');
    Route::post('/admin/chat/toggleasadmin', [AdminController::class, 'AdminChatToggleAsAdmin'])->name('admin.chat.toggleasadmin');
    Route::post('/admin/chat/removefromgroup', [AdminController::class, 'AdminChatRemoveFromGroup'])->name('admin.chat.removefromgroup');
    Route::post('/admin/chat/leaveconversation', [AdminController::class, 'AdminChatLeaveConversation'])->name('admin.chat.leaveconversation');
    Route::get('/admin/chat/checkwhoseen', [AdminController::class, 'AdminChatCheckWhoSeen'])->name('admin.chat.checkwhoseen');
    Route::post('/admin/chat/unsetchatishere', [AdminController::class, 'AdminChatUnsetChatIsHere'])->name('admin.chat.unsetchatishere');
    Route::post('/admin/chat/setchatishere', [AdminController::class, 'AdminChatSetChatIsHere'])->name('admin.chat.setchatishere');
    Route::post('/admin/chat/removeishere', [AdminController::class, 'AdminChatRemoveIsHere'])->name('admin.chat.removeishere');
    Route::get('/admin/chat/gettaskinfo', [AdminController::class, 'AdminChatGetTaskInfo'])->name('admin.chat.gettaskinfo');
//endregion


    Route::get('/admin/log', [AdminController::class, 'AdminSystemLog'])->name('admin.log');

}); // End Group Admin Middler

Route::middleware(['auth','role:head'])->group(function() {
    Route::get('/head/getallchats', [HeadController::class, 'HeadDashboardGetAllChats'])->name('head.getallchats');
    Route::get('/head/getallnotification', [HeadController::class, 'HeadDashboardGetAllNotification'])->name('head.getallnotification');
    Route::post('/head/markasreadednotification', [HeadController::class, 'HeadDashboardMarkAsReadedNotification'])->name('head.markasreadednotification');
    Route::post('/head/clearnotification', [HeadController::class, 'HeadDashboardClearNotification'])->name('head.clearnotification');

    Route::get('/head/dashboard', [HeadController::class, 'HeadDashboard'])->name('head.dashboard');

    Route::post('/head/tasks/reload-myongoing-div', [HeadController::class, 'reloadMyOngoingDiv'])->name('reloadhe.myongoing.div');

    Route::get('/head/profile', [HeadController::class, 'HeadProfile'])->name('head.profile');
    Route::post('/head/profile/photo', [HeadController::class, 'SavePhoto'])->name('head.profile.photo');
    Route::post('/head/profile/pinfo', [HeadController::class, 'ProfileInfo'])->name('head.profile.pinfo');
    Route::post('/head/profile/binfo', [HeadController::class, 'BasicInfo'])->name('head.profile.binfo');
    Route::post('/head/profile/update', [HeadController::class, 'UpdatePassword'])->name('head.profile.update');

    //region Task
    Route::get('/head/tasks', [HeadController::class, 'HeadTasks'])->name('head.tasks');
    Route::post('/head/tasks/apage', [HeadController::class, 'HeadTasksAddPage'])->name('head.tasks.apage');
    Route::get('/head/tasks/etemp', [HeadController::class, 'HeadTasksEditTemplate'])->name('head.tasks.etemp');
    Route::get('/head/tasks/eitemp', [HeadController::class, 'HeadTasksEditInfoTemplate'])->name('head.tasks.eitemp');
    Route::post('/head/tasks/sptitle', [HeadController::class, 'HeadTasksSavePageTitle'])->name('head.tasks.sptitle');
    Route::post('/head/tasks/stinfo', [HeadController::class, 'HeadTasksSaveTaskInfo'])->name('head.tasks.stinfo');
    Route::post('/head/tasks/create', [HeadController::class, 'HeadTasksCreate'])->name('head.tasks.create');
    Route::post('/head/tasks/rpage', [HeadController::class, 'HeadTasksRemovePage'])->name('head.tasks.rpage');
    Route::post('/head/tasks/afield', [HeadController::class, 'HeadTasksAddField'])->name('head.tasks.afield');
    Route::post('/head/tasks/sfinput', [HeadController::class, 'HeadTasksSaveFieldInput'])->name('head.tasks.sfinput');
    Route::get('/head/tasks/goradio', [HeadController::class, 'HeadTasksGetOptionRadio'])->name('head.tasks.goradio');
    Route::get('/head/tasks/godown', [HeadController::class, 'HeadTasksGetOptionDown'])->name('head.tasks.godown');
    Route::post('/head/tasks/rfrow', [HeadController::class, 'HeadTasksRemoveFieldRow'])->name('head.tasks.rfrow');
    Route::get('/head/tasks/eftask', [HeadController::class, 'HeadTasksEditFieldTask'])->name('head.tasks.eftask');
    Route::post('/head/tasks/cstask', [HeadController::class, 'HeadTasksChangeStepperTask'])->name('head.tasks.cstask');
    Route::get('/head/tasks/vdtdept', [HeadController::class, 'HeadTasksViewDistributeTaskDepartment'])->name('head.tasks.vdtdept');
    Route::post('/head/tasks/sdtdept', [HeadController::class, 'HeadTasksSubmitDistributeTaskDepartment'])->name('head.tasks.sdtdept');
    Route::get('/head/tasks/cdtdept', [HeadController::class, 'HeadTasksCheckDistributeTaskDepartment'])->name('head.tasks.cdtdept');
    Route::get('/head/tasks/vlsatask', [HeadController::class, 'HeadTasksViewListSoloAssignTask'])->name('head.tasks.vlsatask');
    Route::post('/head/tasks/sastask', [HeadController::class, 'HeadTasksSubmitAssignSoloTask'])->name('head.tasks.sastask');
    Route::get('/head/tasks/vlgatask', [HeadController::class, 'HeadTasksViewListGroupAssignTask'])->name('head.tasks.vlgatask');
    Route::post('/head/tasks/atagtask', [HeadController::class, 'HeadTasksAddTemporaryAssignGroupTask'])->name('head.tasks.atagtask');
    Route::post('/head/tasks/rtagtask', [HeadController::class, 'HeadTasksRemoveTemporaryAssignGroupTask'])->name('head.tasks.rtagtask');
    Route::post('/head/tasks/sagtask', [HeadController::class, 'HeadTasksSubmitAssignGroupTask'])->name('head.tasks.sagtask');
    Route::post('/head/tasks/arctemp', [HeadController::class, 'HeadTasksArchiveTemplate'])->name('head.tasks.arctemp');
    Route::post('/head/tasks/reload-ongoing-div', [HeadController::class, 'reloadOngoingDiv'])->name('reloadhe.ongoing.div');
    Route::post('/head/tasks/reload-tocheck-div', [HeadController::class, 'reloadToCheckDiv'])->name('reloadhe.tocheck.div');
    Route::post('/head/tasks/reload-complete-div', [HeadController::class, 'reloadCompleteDiv'])->name('reloadhe.complete.div');
    Route::post('/head/tasks/reload-distribute-div', [HeadController::class, 'reloadDistributeDiv'])->name('reloadhe.distribute.div');
    Route::post('/head/tasks/reload-distributereq-div', [HeadController::class, 'reloadDistributeReqDiv'])->name('reloadhe.distributereq.div');
    Route::post('/head/tasks/reload-overtimereq-div', [HeadController::class, 'reloadOvertimeReqDiv'])->name('reloadhe.overtimereq.div');
    Route::post('/head/tasks/approvetask', [HeadController::class, 'HeadApproveTask'])->name('head.tasks.approvetask');
    Route::post('/head/tasks/declinetask', [HeadController::class, 'HeadDeclineTask'])->name('head.tasks.declinetask');
    Route::post('/head/tasks/adisttask', [HeadController::class, 'HeadAcceptDistributeTask'])->name('head.tasks.adisttask');
    Route::post('/head/tasks/ddisttask', [HeadController::class, 'HeadDeclineDistributeTask'])->name('head.tasks.ddisttask');
    Route::post('/head/tasks/disttask', [HeadController::class, 'HeadDistributeTask'])->name('head.tasks.disttask');
    Route::post('/head/tasks/aadisttask', [HeadController::class, 'HeadAcceptAllDistributeTask'])->name('head.tasks.aadisttask');
    Route::post('/head/tasks/dadisttask', [HeadController::class, 'HeadDeclineAllDistributeTask'])->name('head.tasks.dadisttask');
    Route::get('/head/tasks/vnttlink', [HeadController::class, 'HeadTasksViewNewTaskToLink'])->name('head.tasks.vnttlink');
    Route::get('/head/tasks/checklinktemp', [HeadController::class, 'HeadTasksCheckLinkTemp'])->name('head.tasks.checklinktemp');
    Route::post('/head/tasks/removelinktemp', [HeadController::class, 'HeadTasksRemoveLinkTemp'])->name('head.tasks.removelinktemp');
    Route::post('/head/tasks/addmemberlinktemp', [HeadController::class, 'HeadTasksAddMemberLinkTemp'])->name('head.tasks.addmemberlinktemp');
    Route::post('/head/tasks/removememberlinktemp', [HeadController::class, 'HeadTasksRemoveMemberLinkTemp'])->name('head.tasks.removememberlinktemp');
    Route::post('/head/tasks/linkandassigntask', [HeadController::class, 'HeadTasksLinkAndAssignTask'])->name('head.tasks.linkandassigntask');
    Route::post('/head/tasks/grouplinkandassigntask', [HeadController::class, 'HeadTasksGroupLinkAndAssignTask'])->name('head.tasks.grouplinkandassigntask');
    Route::post('/head/tasks/linkandassignongoingtask', [HeadController::class, 'HeadTasksLinkAndAssignOngoingTask'])->name('head.tasks.linkandassignongoingtask');
    Route::post('/head/tasks/groupchecktempomemberlink', [HeadController::class, 'HeadTasksGroupCheckTempoMemberLink'])->name('head.tasks.groupchecktempomemberlink');
    Route::get('/head/tasks/viewstatistic', [HeadController::class, 'HeadTasksViewStatistic'])->name('head.tasks.viewstatistic');
    Route::post('/head/tasks/setemergencyuserstatus', [HeadController::class, 'HeadTasksSetEmergencyUserStatus'])->name('head.tasks.setemergencyuserstatus');
    Route::get('/head/tasks/getworktimesettings', [HeadController::class, 'HeadTasksGetWorkTimeSettings'])->name('head.tasks.getworktimesettings');
    Route::post('/head/tasks/requestovertimetask', [HeadController::class, 'HeadTasksRequestOvertimeTask'])->name('head.tasks.requestovertimetask');
    Route::post('/head/tasks/acceptovertimerequest', [HeadController::class, 'HeadTasksAcceptOvertimeRequest'])->name('head.tasks.acceptovertimerequest');
    Route::post('/head/tasks/declineovertimerequest', [HeadController::class, 'HeadTasksDeclineOvertimeRequest'])->name('head.tasks.declineovertimerequest');
    Route::post('/head/tasks/archivedistributedtask', [HeadController::class, 'HeadTasksArchiveDistributedTask'])->name('head.tasks.archivedistributedtask');
    Route::post('/head/tasks/archivecompletedtask', [HeadController::class, 'HeadTasksArchiveCompletedTask'])->name('head.tasks.archivecompletedtask');
    Route::post('/head/tasks/retrievetask', [HeadController::class, 'HeadTasksRetrievetask'])->name('head.tasks.retrievetask');
    Route::post('/head/tasks/deletetask', [HeadController::class, 'HeadTasksDeletetask'])->name('head.tasks.deletetask');
    Route::post('/head/tasks/retrievetemp', [HeadController::class, 'HeadTasksRetrievetemp'])->name('head.tasks.retrievetemp');
    Route::post('/head/tasks/deletetemp', [HeadController::class, 'HeadTasksDeletetemp'])->name('head.tasks.deletetemp');
    Route::post('/head/chat/sendtaskcontactmessage', [HeadController::class, 'HeadChatSendTaskContactMessage'])->name('head.chat.sendtaskcontactmessage');

    Route::get('/head/lvtasks/{task}', [HeadController::class, 'HeadLiveViewTasks'])->name('head.lvtasks');
    Route::get('/head/tasks/glvtasks', [HeadController::class, 'HeadGetLiveViewTasks'])->name('head.tasks.glvtasks');
    Route::get('/head/tasks/getradio', [HeadController::class, 'HeadTasksGetRadio'])->name('head.tasks.getradio');
    Route::get('/head/tasks/getdown', [HeadController::class, 'HeadTasksGetDown'])->name('head.tasks.getdown');
    Route::post('/head/tasks/livereloading', [HeadController::class, 'HeadTasksLiveReloading'])->name('head.tasks.livereloading');
    Route::post('/head/tasks/unlinktask', [HeadController::class, 'HeadTasksUnlinkTask'])->name('head.tasks.unlinktask');

    Route::get('/head/ptasks/{task}', [HeadController::class, 'HeadPrintTasks'])->name('head.ptasks');
    Route::get('/head/tasks/gptasks', [HeadController::class, 'HeadGetPrintTasks'])->name('head.tasks.gptasks');
    Route::get('/head/tasks/printgetradio', [HeadController::class, 'HeadTasksPrintGetRadio'])->name('head.tasks.printgetradio');
    Route::get('/head/tasks/printgetdown', [HeadController::class, 'HeadTasksPrintGetDown'])->name('head.tasks.printgetdown');
    Route::post('/head/tasks/liveprintreloading', [HeadController::class, 'HeadTasksLivePrintReloading'])->name('head.tasks.liveprintreloading');
    //endregion

    //region Department
    Route::get('/head/department', [HeadController::class, 'HeadDepartment'])->name('head.department');
    Route::post('/head/department/add', [HeadController::class, 'HeadAddDepartment'])->name('head.department.add');
    Route::get('/head/department/user', [HeadController::class, 'HeadGetUserDepartments'])->name('head.department.user');
    Route::get('/head/department/head', [HeadController::class, 'HeadGetDepartmentHead'])->name('head.department.head');
    Route::get('/head/department/member', [HeadController::class, 'HeadGetDepartmentMember'])->name('head.department.member');
    Route::get('/head/department/vhead', [HeadController::class, 'HeadGetDepartmentVHead'])->name('head.department.vhead');
    Route::get('/head/department/vmember', [HeadController::class, 'HeadGetDepartmentVMember'])->name('head.department.vmember');
    Route::get('/head/department/edept', [HeadController::class, 'HeadGetDepartmentEDept'])->name('head.department.edept');
    Route::post('/head/department/edit', [HeadController::class, 'HeadEditDepartment'])->name('head.department.edit');
    Route::post('/head/department/rdept', [HeadController::class, 'HeadDepartmentRDept'])->name('head.department.rdept');
    Route::get('/head/department/vuser', [HeadController::class, 'HeadGetDepartmentVUser'])->name('head.department.vuser');
    Route::get('/head/department/muser', [HeadController::class, 'HeadGetDepartmentMUser'])->name('head.department.muser');
    Route::post('/head/department/assign', [HeadController::class, 'HeadAssignDepartmentMember'])->name('head.department.assign');
    Route::post('/head/department/remove', [HeadController::class, 'HeadRemoveDepartmentMember'])->name('head.department.remove');
    //endregion

    //region Chat
    Route::get('/head/chat', [HeadController::class, 'HeadChat'])->name('head.chat');
    Route::post('/head/chat/sendcontactmessage', [HeadController::class, 'HeadChatSendContactMessage'])->name('head.chat.sendcontactmessage');
    Route::post('/head/chat/reload-chat-list', [HeadController::class, 'reloadChatList'])->name('reloadhe.chat.list');
    Route::get('/head/chat/viewchats', [HeadController::class, 'HeadViewChats'])->name('head.chat.viewchats');
    Route::post('/head/chat/sendmessage', [HeadController::class, 'HeadChatSendMessage'])->name('head.chat.sendmessage');
    Route::post('/head/chat/reload-chat-message', [HeadController::class, 'reloadChatMessage'])->name('reloadhe.chat.message');
    Route::get('/head/chat/checkmessageattachment', [HeadController::class, 'HeadCheckMessageAttachment'])->name('head.chat.checkmessageattachment');
    Route::get('/head/chat/replieduser', [HeadController::class, 'HeadChatRepliedUser'])->name('head.chat.replieduser');
    Route::get('/head/chat/checkmessagereply', [HeadController::class, 'HeadcheckMessageReply'])->name('head.chat.checkmessagereply');
    Route::get('/head/chat/geteditmessage', [HeadController::class, 'HeadGetEditMessage'])->name('head.chat.geteditmessage');
    Route::post('/head/chat/editmessage', [HeadController::class, 'HeadChatEditMessage'])->name('head.chat.editmessage');
    Route::get('/head/chat/vieweditmessage', [HeadController::class, 'HeadViewEditMessage'])->name('head.chat.vieweditmessage');
    Route::get('/head/chat/viewmessagecontact', [HeadController::class, 'HeadViewMessageContact'])->name('head.chat.viewmessagecontact');
    Route::post('/head/chat/sendforwardmessage', [HeadController::class, 'HeadChatSendForwardMessage'])->name('head.chat.sendforwardmessage');
    Route::get('/head/chat/checkwhoforward', [HeadController::class, 'HeadCheckWhoForward'])->name('head.chat.checkwhoforward');
    Route::post('/head/chat/unsendmessage', [HeadController::class, 'HeadChatUnsendMessage'])->name('head.chat.unsendmessage');
    Route::post('/head/chat/pinmessage', [HeadController::class, 'HeadChatPinMessage'])->name('head.chat.pinmessage');
    Route::post('/head/chat/unpinmessage', [HeadController::class, 'HeadChatUnpinMessage'])->name('head.chat.unpinmessage');
    Route::post('/head/chat/react', [HeadController::class, 'HeadChatReact'])->name('head.chat.react');
    Route::post('/head/chat/reload-chat-pinned', [HeadController::class, 'reloadChatPinned'])->name('reloadhe.chat.pinned');
    Route::get('/head/chat/viewpinnedmessage', [HeadController::class, 'HeadViewPinnedMessage'])->name('head.chat.viewpinnedmessage');
    Route::post('/head/chat/setnickname', [HeadController::class, 'HeadChatSetNickname'])->name('head.chat.setnickname');
    Route::post('/head/chat/setmutedchat', [HeadController::class, 'HeadChatSetMutedChat'])->name('head.chat.setmutedchat');
    Route::post('/head/chat/searchmessagevalue', [HeadController::class, 'HeadChatSearchMessageValue'])->name('head.chat.searchmessagevalue');
    Route::get('/head/chat/{chat}/media', [HeadController::class, 'getChatMedia'])->name('head.chat.media');
    Route::post('/head/chat/savemeeting', [HeadController::class, 'HeadChatSaveMeeting'])->name('head.chat.savemeeting');
    Route::post('/head/chat/removemeeting', [HeadController::class, 'HeadChatRemoveMeeting'])->name('head.chat.removemeeting');
    Route::post('/head/chat/creategroupmessage', [HeadController::class, 'HeadChatCreateGroupMessage'])->name('head.chat.creategroupmessage');
    Route::post('/head/chat/setconvoimage', [HeadController::class, 'HeadChatSetConvoImage'])->name('head.chat.setconvoimage');
    Route::post('/head/chat/unsetconvoimage', [HeadController::class, 'HeadChatUnsetConvoImage'])->name('head.chat.unsetconvoimage');
    Route::post('/head/chat/setconvoname', [HeadController::class, 'HeadChatSetConvoName'])->name('head.chat.setconvoname');
    Route::post('/head/chat/unsetconvoname', [HeadController::class, 'HeadChatUnsetConvoName'])->name('head.chat.unsetconvoname');
    Route::post('/head/chat/addnewmembergroup', [HeadController::class, 'HeadChatAddNewMemberGroup'])->name('head.chat.addnewmembergroup');
    Route::post('/head/chat/toggleasadmin', [HeadController::class, 'HeadChatToggleAsAdmin'])->name('head.chat.toggleasadmin');
    Route::post('/head/chat/removefromgroup', [HeadController::class, 'HeadChatRemoveFromGroup'])->name('head.chat.removefromgroup');
    Route::post('/head/chat/leaveconversation', [HeadController::class, 'HeadChatLeaveConversation'])->name('head.chat.leaveconversation');
    Route::get('/head/chat/checkwhoseen', [HeadController::class, 'HeadChatCheckWhoSeen'])->name('head.chat.checkwhoseen');
    Route::post('/head/chat/unsetchatishere', [HeadController::class, 'HeadChatUnsetChatIsHere'])->name('head.chat.unsetchatishere');
    Route::post('/head/chat/setchatishere', [HeadController::class, 'HeadChatSetChatIsHere'])->name('head.chat.setchatishere');
    Route::post('/head/chat/removeishere', [HeadController::class, 'HeadChatRemoveIsHere'])->name('head.chat.removeishere');
    Route::get('/head/chat/gettaskinfo', [HeadController::class, 'HeadChatGetTaskInfo'])->name('head.chat.gettaskinfo');
    //endregion

    //region Calendar

    Route::get('/head/calendar', [HeadController::class, 'HeadCalendar'])->name('head.calendar');
    Route::get('/head/calendar/viewtaskdate', [HeadController::class, 'HeadCalendarViewTaskDate'])->name('head.calendar.viewtaskdate');
    Route::post('/head/calendar/saveevent', [HeadController::class, 'HeadCalendarSaveEvent'])->name('head.calendar.saveevent');
    Route::get('/head/calendar/viewprivateeventdate', [HeadController::class, 'HeadCalendarViewPrivateEventDate'])->name('head.calendar.viewprivateeventdate');
    Route::get('/head/calendar/viewdepartmenteventdate', [HeadController::class, 'HeadCalendarViewDepartmentEventDate'])->name('head.calendar.viewdepartmenteventdate');
    Route::post('/head/calendar/removeevent', [HeadController::class, 'HeadCalendarRemoveEvent'])->name('head.calendar.removeevent');
    Route::get('/head/calendar/viewannouncementeventdate', [HeadController::class, 'HeadCalendarViewAnnouncementEventDate'])->name('head.calendar.viewannouncementeventdate');

    //endregion

    Route::get('/head/logout', [HeadController::class, 'HeadLogout'])->name('head.logout');
});// End Group Head Middler

Route::middleware(['auth','role:observer'])->group(function() {
    Route::get('/manager/getallnotes', [ObserverController::class, 'ObserverDashboardGetAllNotes'])->name('observer.getallnotes');
    Route::get('/manager/getallchats', [ObserverController::class, 'ObserverDashboardGetAllChats'])->name('observer.getallchats');
    Route::get('/manager/getallfeedback', [ObserverController::class, 'ObserverDashboardGetAllFeedback'])->name('observer.getallfeedback');
    Route::get('/manager/getallnotification', [ObserverController::class, 'ObserverDashboardGetAllNotification'])->name('observer.getallnotification');
    Route::post('/manager/markasreadednotification', [ObserverController::class, 'ObserverDashboardMarkAsReadedNotification'])->name('observer.markasreadednotification');
    Route::post('/manager/clearnotification', [ObserverController::class, 'ObserverDashboardClearNotification'])->name('observer.clearnotification');
    Route::post('/manager/setreviewedfeed', [ObserverController::class, 'ObserverDashboardSetReviewedFeed'])->name('observer.setreviewedfeed');
    Route::post('/manager/removefeed', [ObserverController::class, 'ObserverDashboardRemoveFeed'])->name('observer.removefeed');

    Route::get('/manager/dashboard', [ObserverController::class, 'ObserverDashboard'])->name('observer.dashboard');
    Route::get('/manager/profile', [ObserverController::class, 'ObserverProfile'])->name('observer.profile');
    Route::post('/manager/profile/photo', [ObserverController::class, 'SavePhoto'])->name('observer.profile.photo');
    Route::post('/manager/profile/pinfo', [ObserverController::class, 'ProfileInfo'])->name('observer.profile.pinfo');
    Route::post('/manager/profile/binfo', [ObserverController::class, 'BasicInfo'])->name('observer.profile.binfo');
    Route::post('/manager/profile/update', [ObserverController::class, 'UpdatePassword'])->name('observer.profile.update');
    Route::post('/manager/tasks/reload-myongoing-div', [ObserverController::class, 'reloadMyOngoingDiv'])->name('reloadobs.myongoing.div');

    //region Task
    Route::get('/manager/tasks', [ObserverController::class, 'ObserverTasks'])->name('observer.tasks');
    Route::post('/manager/tasks/apage', [ObserverController::class, 'ObserverTasksAddPage'])->name('observer.tasks.apage');
    Route::get('/manager/tasks/etemp', [ObserverController::class, 'ObserverTasksEditTemplate'])->name('observer.tasks.etemp');
    Route::get('/manager/tasks/eitemp', [ObserverController::class, 'ObserverTasksEditInfoTemplate'])->name('observer.tasks.eitemp');
    Route::post('/manager/tasks/sptitle', [ObserverController::class, 'ObserverTasksSavePageTitle'])->name('observer.tasks.sptitle');
    Route::post('/manager/tasks/stinfo', [ObserverController::class, 'ObserverTasksSaveTaskInfo'])->name('observer.tasks.stinfo');
    Route::post('/manager/tasks/create', [ObserverController::class, 'ObserverTasksCreate'])->name('observer.tasks.create');
    Route::post('/manager/tasks/rpage', [ObserverController::class, 'ObserverTasksRemovePage'])->name('observer.tasks.rpage');
    Route::post('/manager/tasks/afield', [ObserverController::class, 'ObserverTasksAddField'])->name('observer.tasks.afield');
    Route::post('/manager/tasks/sfinput', [ObserverController::class, 'ObserverTasksSaveFieldInput'])->name('observer.tasks.sfinput');
    Route::get('/manager/tasks/goradio', [ObserverController::class, 'ObserverTasksGetOptionRadio'])->name('observer.tasks.goradio');
    Route::get('/manager/tasks/godown', [ObserverController::class, 'ObserverTasksGetOptionDown'])->name('observer.tasks.godown');
    Route::post('/manager/tasks/rfrow', [ObserverController::class, 'ObserverTasksRemoveFieldRow'])->name('observer.tasks.rfrow');
    Route::get('/manager/tasks/eftask', [ObserverController::class, 'ObserverTasksEditFieldTask'])->name('observer.tasks.eftask');
    Route::post('/manager/tasks/cstask', [ObserverController::class, 'ObserverTasksChangeStepperTask'])->name('observer.tasks.cstask');
    Route::get('/manager/tasks/vdtdept', [ObserverController::class, 'ObserverTasksViewDistributeTaskDepartment'])->name('observer.tasks.vdtdept');
    Route::post('/manager/tasks/sdtdept', [ObserverController::class, 'ObserverTasksSubmitDistributeTaskDepartment'])->name('observer.tasks.sdtdept');
    Route::get('/manager/tasks/cdtdept', [ObserverController::class, 'ObserverTasksCheckDistributeTaskDepartment'])->name('observer.tasks.cdtdept');
    Route::get('/manager/tasks/vlsatask', [ObserverController::class, 'ObserverTasksViewListSoloAssignTask'])->name('observer.tasks.vlsatask');
    Route::post('/manager/tasks/sastask', [ObserverController::class, 'ObserverTasksSubmitAssignSoloTask'])->name('observer.tasks.sastask');
    Route::get('/manager/tasks/vlgatask', [ObserverController::class, 'ObserverTasksViewListGroupAssignTask'])->name('observer.tasks.vlgatask');
    Route::post('/manager/tasks/atagtask', [ObserverController::class, 'ObserverTasksAddTemporaryAssignGroupTask'])->name('observer.tasks.atagtask');
    Route::post('/manager/tasks/rtagtask', [ObserverController::class, 'ObserverTasksRemoveTemporaryAssignGroupTask'])->name('observer.tasks.rtagtask');
    Route::post('/manager/tasks/sagtask', [ObserverController::class, 'ObserverTasksSubmitAssignGroupTask'])->name('observer.tasks.sagtask');
    Route::get('/manager/tasks/vlsaatask', [ObserverController::class, 'ObserverTasksViewListSoloAutomationAssignTask'])->name('observer.tasks.vlsaatask');
    Route::post('/manager/tasks/slsaatask', [ObserverController::class, 'ObserverTasksSubmitListSoloAutomationAssignTask'])->name('observer.tasks.slsaatask');
    Route::get('/manager/tasks/vlgaatask', [ObserverController::class, 'ObserverTasksViewListGroupAutomationAssignTask'])->name('observer.tasks.vlgaatask');
    Route::post('/manager/tasks/slgaatask', [ObserverController::class, 'ObserverTasksSubmitListGroupAutomationAssignTask'])->name('observer.tasks.slgaatask');
    Route::post('/manager/tasks/rtaagtask', [ObserverController::class, 'ObserverTasksRemoveTemporaryAutomationAssignGroupTask'])->name('observer.tasks.rtaagtask');
    Route::post('/manager/tasks/rtaastask', [ObserverController::class, 'ObserverTasksRemoveTemporaryAutomationAssignSoloTask'])->name('observer.tasks.rtaastask');
    Route::post('/manager/tasks/rtaagltask', [ObserverController::class, 'ObserverTasksRemoveTemporaryAutomationAssignGroupListTask'])->name('observer.tasks.rtaagltask');
    Route::post('/manager/tasks/slglaatask', [ObserverController::class, 'ObserverTasksSubmitListGroupMemberAutomationAssignTask'])->name('observer.tasks.slglaatask');
    Route::post('/manager/tasks/arctemp', [ObserverController::class, 'ObserverTasksArchiveTemplate'])->name('observer.tasks.arctemp');
    Route::post('/manager/tasks/reload-ongoing-div', [ObserverController::class, 'reloadOngoingDiv'])->name('reloadobs.ongoing.div');
    Route::post('/manager/tasks/reload-tocheck-div', [ObserverController::class, 'reloadToCheckDiv'])->name('reloadobs.tocheck.div');
    Route::post('/manager/tasks/reload-complete-div', [ObserverController::class, 'reloadCompleteDiv'])->name('reloadobs.complete.div');
    Route::post('/manager/tasks/reload-distribute-div', [ObserverController::class, 'reloadDistributeDiv'])->name('reloadobs.distribute.div');
    Route::post('/manager/tasks/reload-distributereq-div', [ObserverController::class, 'reloadDistributeReqDiv'])->name('reloadobs.distributereq.div');
    Route::post('/manager/tasks/reload-overtimereq-div', [ObserverController::class, 'reloadOvertimeReqDiv'])->name('reloadobs.overtimereq.div');
    Route::get('/manager/tasks/vtctask', [ObserverController::class, 'ObserverTasksViewListTaskCheckingAutomation'])->name('observer.tasks.vtctask');
    Route::post('/manager/tasks/satctask', [ObserverController::class, 'ObserverSubmitAutomationToCheckTask'])->name('observer.tasks.satctask');
    Route::post('/manager/tasks/ratctask', [ObserverController::class, 'ObserverRemoveAutomationToCheckTask'])->name('observer.tasks.ratctask');
    Route::post('/manager/tasks/approvetask', [ObserverController::class, 'ObserverApproveTask'])->name('observer.tasks.approvetask');
    Route::post('/manager/tasks/declinetask', [ObserverController::class, 'ObserverDeclineTask'])->name('observer.tasks.declinetask');
    Route::get('/manager/tasks/vdtask', [ObserverController::class, 'ObserverTasksDistributionAutomation'])->name('observer.tasks.vdtask');
    Route::post('/manager/tasks/sadtask', [ObserverController::class, 'ObserverSubmitAutomationDistributeTask'])->name('observer.tasks.sadtask');
    Route::post('/manager/tasks/radtask', [ObserverController::class, 'ObserverRemoveAutomationDistributeTask'])->name('observer.tasks.radtask');
    Route::post('/manager/tasks/adisttask', [ObserverController::class, 'ObserverAcceptDistributeTask'])->name('observer.tasks.adisttask');
    Route::post('/manager/tasks/ddisttask', [ObserverController::class, 'ObserverDeclineDistributeTask'])->name('observer.tasks.ddisttask');
    Route::post('/manager/tasks/disttask', [ObserverController::class, 'ObserverDistributeTask'])->name('observer.tasks.disttask');
    Route::post('/manager/tasks/aadisttask', [ObserverController::class, 'ObserverAcceptAllDistributeTask'])->name('observer.tasks.aadisttask');
    Route::post('/manager/tasks/dadisttask', [ObserverController::class, 'ObserverDeclineAllDistributeTask'])->name('observer.tasks.dadisttask');
    Route::get('/manager/tasks/vaadtask', [ObserverController::class, 'ObserverTasksAcceptDistributionAutomation'])->name('observer.tasks.vaadtask');
    Route::post('/manager/tasks/saadtask', [ObserverController::class, 'ObserverTasksSubmitAcceptDistributionAutomation'])->name('observer.tasks.saadtask');
    Route::post('/manager/tasks/raadtask', [ObserverController::class, 'ObserverTasksRemoveAcceptDistributionAutomation'])->name('observer.tasks.raadtask');
    Route::get('/manager/tasks/vnttlink', [ObserverController::class, 'ObserverTasksViewNewTaskToLink'])->name('observer.tasks.vnttlink');
    Route::get('/manager/tasks/checklinktemp', [ObserverController::class, 'ObserverTasksCheckLinkTemp'])->name('observer.tasks.checklinktemp');
    Route::post('/manager/tasks/removelinktemp', [ObserverController::class, 'ObserverTasksRemoveLinkTemp'])->name('observer.tasks.removelinktemp');
    Route::post('/manager/tasks/addmemberlinktemp', [ObserverController::class, 'ObserverTasksAddMemberLinkTemp'])->name('observer.tasks.addmemberlinktemp');
    Route::post('/manager/tasks/removememberlinktemp', [ObserverController::class, 'ObserverTasksRemoveMemberLinkTemp'])->name('observer.tasks.removememberlinktemp');
    Route::post('/manager/tasks/linkandassigntask', [ObserverController::class, 'ObserverTasksLinkAndAssignTask'])->name('observer.tasks.linkandassigntask');
    Route::post('/manager/tasks/grouplinkandassigntask', [ObserverController::class, 'ObserverTasksGroupLinkAndAssignTask'])->name('observer.tasks.grouplinkandassigntask');
    Route::post('/manager/tasks/linkandassignongoingtask', [ObserverController::class, 'ObserverTasksLinkAndAssignOngoingTask'])->name('observer.tasks.linkandassignongoingtask');
    Route::get('/manager/tasks/viewlinkauto', [ObserverController::class, 'ObserverTasksViewLinkAuto'])->name('observer.tasks.viewlinkauto');
    Route::post('/manager/tasks/setsololinkauto', [ObserverController::class, 'ObserverTasksSetSoloLinkAuto'])->name('observer.tasks.setsololinkauto');
    Route::post('/manager/tasks/removesololinkauto', [ObserverController::class, 'ObserverTasksRemoveSoloLinkAuto'])->name('observer.tasks.removesololinkauto');
    Route::post('/manager/tasks/addtempogrouplinkauto', [ObserverController::class, 'ObserverTasksAddTempoGroupLinkAuto'])->name('observer.tasks.addtempogrouplinkauto');
    Route::post('/manager/tasks/removetempogrouplinkauto', [ObserverController::class, 'ObserverTasksRemoveTempoGroupLinkAuto'])->name('observer.tasks.removetempogrouplinkauto');
    Route::post('/manager/tasks/groupchecktempomemberlink', [ObserverController::class, 'ObserverTasksGroupCheckTempoMemberLink'])->name('observer.tasks.groupchecktempomemberlink');
    Route::post('/manager/tasks/setgrouplinkauto', [ObserverController::class, 'ObserverTasksSetGroupLinkAuto'])->name('observer.tasks.setgrouplinkauto');
    Route::post('/manager/tasks/removegrouplinkauto', [ObserverController::class, 'ObserverTasksRemoveGroupLinkAuto'])->name('observer.tasks.removegrouplinkauto');
    Route::get('/manager/tasks/viewstatistic', [ObserverController::class, 'ObserverTasksViewStatistic'])->name('observer.tasks.viewstatistic');
    Route::post('/manager/tasks/setemergencyuserstatus', [ObserverController::class, 'ObserverTasksSetEmergencyUserStatus'])->name('observer.tasks.setemergencyuserstatus');
    Route::post('/manager/tasks/setovertimeautomation', [ObserverController::class, 'ObserverTasksSetOverTimeAutomation'])->name('observer.tasks.setovertimeautomation');
    Route::get('/manager/tasks/getworktimesettings', [ObserverController::class, 'ObserverTasksGetWorkTimeSettings'])->name('observer.tasks.getworktimesettings');
    Route::post('/manager/tasks/setworktimeautomation', [ObserverController::class, 'ObserverTasksSetWorkTimeAutomation'])->name('observer.tasks.setworktimeautomation');
    Route::post('/manager/tasks/requestovertimetask', [ObserverController::class, 'ObserverTasksRequestOvertimeTask'])->name('observer.tasks.requestovertimetask');
    Route::post('/manager/tasks/acceptovertimerequest', [ObserverController::class, 'ObserverTasksAcceptOvertimeRequest'])->name('observer.tasks.acceptovertimerequest');
    Route::post('/manager/tasks/declineovertimerequest', [ObserverController::class, 'ObserverTasksDeclineOvertimeRequest'])->name('observer.tasks.declineovertimerequest');
    Route::post('/manager/tasks/archivedistributedtask', [ObserverController::class, 'ObserverTasksArchiveDistributedTask'])->name('observer.tasks.archivedistributedtask');
    Route::post('/manager/tasks/archivecompletedtask', [ObserverController::class, 'ObserverTasksArchiveCompletedTask'])->name('observer.tasks.archivecompletedtask');
    Route::post('/manager/tasks/retrievetask', [ObserverController::class, 'ObserverTasksRetrievetask'])->name('observer.tasks.retrievetask');
    Route::post('/manager/tasks/deletetask', [ObserverController::class, 'ObserverTasksDeletetask'])->name('observer.tasks.deletetask');
    Route::post('/manager/tasks/retrievetemp', [ObserverController::class, 'ObserverTasksRetrievetemp'])->name('observer.tasks.retrievetemp');
    Route::post('/manager/tasks/deletetemp', [ObserverController::class, 'ObserverTasksDeletetemp'])->name('observer.tasks.deletetemp');
    Route::post('/manager/chat/sendtaskcontactmessage', [ObserverController::class, 'ObserverChatSendTaskContactMessage'])->name('observer.chat.sendtaskcontactmessage');

    Route::get('/manager/etasks/{task}', [ObserverController::class, 'ObserverEditTasks'])->name('observer.etasks');
    Route::post('/manager/etasks/save', [ObserverController::class, 'ObserverEditSaveTasks'])->name('observer.etasks.save');
    Route::post('/manager/etasks/userstatuschecking', [ObserverController::class, 'ObserverEditUserStatusCheckingTasks'])->name('observer.etasks.userstatuschecking');

    Route::get('/manager/lvtasks/{task}', [ObserverController::class, 'ObserverLiveViewTasks'])->name('observer.lvtasks');
    Route::get('/manager/tasks/glvtasks', [ObserverController::class, 'ObserverGetLiveViewTasks'])->name('observer.tasks.glvtasks');
    Route::get('/manager/tasks/getradio', [ObserverController::class, 'ObserverTasksGetRadio'])->name('observer.tasks.getradio');
    Route::get('/manager/tasks/getdown', [ObserverController::class, 'ObserverTasksGetDown'])->name('observer.tasks.getdown');
    Route::post('/manager/tasks/livereloading', [ObserverController::class, 'ObserverTasksLiveReloading'])->name('observer.tasks.livereloading');
    Route::post('/manager/tasks/unlinktask', [ObserverController::class, 'ObserverTasksUnlinkTask'])->name('observer.tasks.unlinktask');

    Route::get('/manager/ptasks/{task}', [ObserverController::class, 'ObserverPrintTasks'])->name('observer.ptasks');
    Route::get('/manager/tasks/gptasks', [ObserverController::class, 'ObserverGetPrintTasks'])->name('observer.tasks.gptasks');
    Route::get('/manager/tasks/printgetradio', [ObserverController::class, 'ObserverTasksPrintGetRadio'])->name('observer.tasks.printgetradio');
    Route::get('/manager/tasks/printgetdown', [ObserverController::class, 'ObserverTasksPrintGetDown'])->name('observer.tasks.printgetdown');
    Route::post('/manager/tasks/liveprintreloading', [ObserverController::class, 'ObserverTasksLivePrintReloading'])->name('observer.tasks.liveprintreloading');

    //endregion

    Route::get('/manager/department', [ObserverController::class, 'ObserverDepartment'])->name('observer.department');
    Route::post('/manager/department/account', [ObserverController::class, 'ObserverAddAccount'])->name('observer.department.account');

    //region Chat
        Route::get('/manager/chat', [ObserverController::class, 'ObserverChat'])->name('observer.chat');
        Route::post('/manager/chat/sendcontactmessage', [ObserverController::class, 'ObserverChatSendContactMessage'])->name('observer.chat.sendcontactmessage');
        Route::post('/manager/chat/reload-chat-list', [ObserverController::class, 'reloadChatList'])->name('reloadobs.chat.list');
        Route::get('/manager/chat/viewchats', [ObserverController::class, 'ObserverViewChats'])->name('observer.chat.viewchats');
        Route::post('/manager/chat/sendmessage', [ObserverController::class, 'ObserverChatSendMessage'])->name('observer.chat.sendmessage');
        Route::post('/manager/chat/reload-chat-message', [ObserverController::class, 'reloadChatMessage'])->name('reloadobs.chat.message');
        Route::get('/manager/chat/checkmessageattachment', [ObserverController::class, 'ObserverCheckMessageAttachment'])->name('observer.chat.checkmessageattachment');
        Route::get('/manager/chat/replieduser', [ObserverController::class, 'ObserverChatRepliedUser'])->name('observer.chat.replieduser');
        Route::get('/manager/chat/checkmessagereply', [ObserverController::class, 'ObservercheckMessageReply'])->name('observer.chat.checkmessagereply');
        Route::get('/manager/chat/geteditmessage', [ObserverController::class, 'ObserverGetEditMessage'])->name('observer.chat.geteditmessage');
        Route::post('/manager/chat/editmessage', [ObserverController::class, 'ObserverChatEditMessage'])->name('observer.chat.editmessage');
        Route::get('/manager/chat/vieweditmessage', [ObserverController::class, 'ObserverViewEditMessage'])->name('observer.chat.vieweditmessage');
        Route::get('/manager/chat/viewmessagecontact', [ObserverController::class, 'ObserverViewMessageContact'])->name('observer.chat.viewmessagecontact');
        Route::post('/manager/chat/sendforwardmessage', [ObserverController::class, 'ObserverChatSendForwardMessage'])->name('observer.chat.sendforwardmessage');
        Route::get('/manager/chat/checkwhoforward', [ObserverController::class, 'ObserverCheckWhoForward'])->name('observer.chat.checkwhoforward');
        Route::post('/manager/chat/unsendmessage', [ObserverController::class, 'ObserverChatUnsendMessage'])->name('observer.chat.unsendmessage');
        Route::post('/manager/chat/pinmessage', [ObserverController::class, 'ObserverChatPinMessage'])->name('observer.chat.pinmessage');
        Route::post('/manager/chat/unpinmessage', [ObserverController::class, 'ObserverChatUnpinMessage'])->name('observer.chat.unpinmessage');
        Route::post('/manager/chat/react', [ObserverController::class, 'ObserverChatReact'])->name('observer.chat.react');
        Route::post('/manager/chat/reload-chat-pinned', [ObserverController::class, 'reloadChatPinned'])->name('reloadobs.chat.pinned');
        Route::get('/manager/chat/viewpinnedmessage', [ObserverController::class, 'ObserverViewPinnedMessage'])->name('observer.chat.viewpinnedmessage');
        Route::post('/manager/chat/setnickname', [ObserverController::class, 'ObserverChatSetNickname'])->name('observer.chat.setnickname');
        Route::post('/manager/chat/setmutedchat', [ObserverController::class, 'ObserverChatSetMutedChat'])->name('observer.chat.setmutedchat');
        Route::post('/manager/chat/searchmessagevalue', [ObserverController::class, 'ObserverChatSearchMessageValue'])->name('observer.chat.searchmessagevalue');
        Route::get('/manager/chat/{chat}/media', [ObserverController::class, 'getChatMedia'])->name('observer.chat.media');
        Route::post('/manager/chat/savemeeting', [ObserverController::class, 'ObserverChatSaveMeeting'])->name('observer.chat.savemeeting');
        Route::post('/manager/chat/removemeeting', [ObserverController::class, 'ObserverChatRemoveMeeting'])->name('observer.chat.removemeeting');
        Route::post('/manager/chat/creategroupmessage', [ObserverController::class, 'ObserverChatCreateGroupMessage'])->name('observer.chat.creategroupmessage');
        Route::post('/manager/chat/setconvoimage', [ObserverController::class, 'ObserverChatSetConvoImage'])->name('observer.chat.setconvoimage');
        Route::post('/manager/chat/unsetconvoimage', [ObserverController::class, 'ObserverChatUnsetConvoImage'])->name('observer.chat.unsetconvoimage');
        Route::post('/manager/chat/setconvoname', [ObserverController::class, 'ObserverChatSetConvoName'])->name('observer.chat.setconvoname');
        Route::post('/manager/chat/unsetconvoname', [ObserverController::class, 'ObserverChatUnsetConvoName'])->name('observer.chat.unsetconvoname');
        Route::post('/manager/chat/addnewmembergroup', [ObserverController::class, 'ObserverChatAddNewMemberGroup'])->name('observer.chat.addnewmembergroup');
        Route::post('/manager/chat/toggleasadmin', [ObserverController::class, 'ObserverChatToggleAsAdmin'])->name('observer.chat.toggleasadmin');
        Route::post('/manager/chat/removefromgroup', [ObserverController::class, 'ObserverChatRemoveFromGroup'])->name('observer.chat.removefromgroup');
        Route::post('/manager/chat/leaveconversation', [ObserverController::class, 'ObserverChatLeaveConversation'])->name('observer.chat.leaveconversation');
        Route::get('/manager/chat/checkwhoseen', [ObserverController::class, 'ObserverChatCheckWhoSeen'])->name('observer.chat.checkwhoseen');
        Route::post('/manager/chat/unsetchatishere', [ObserverController::class, 'ObserverChatUnsetChatIsHere'])->name('observer.chat.unsetchatishere');
        Route::post('/manager/chat/setchatishere', [ObserverController::class, 'ObserverChatSetChatIsHere'])->name('observer.chat.setchatishere');
        Route::post('/manager/chat/removeishere', [ObserverController::class, 'ObserverChatRemoveIsHere'])->name('observer.chat.removeishere');
        Route::get('/manager/chat/gettaskinfo', [ObserverController::class, 'ObserverChatGetTaskInfo'])->name('observer.chat.gettaskinfo');
    //endregion

    //region Calendar

    Route::get('/manager/calendar', [ObserverController::class, 'ObserverCalendar'])->name('observer.calendar');
    Route::get('/manager/calendar/viewtaskdate', [ObserverController::class, 'ObserverCalendarViewTaskDate'])->name('observer.calendar.viewtaskdate');
    Route::post('/manager/calendar/saveevent', [ObserverController::class, 'ObserverCalendarSaveEvent'])->name('observer.calendar.saveevent');
    Route::get('/manager/calendar/viewprivateeventdate', [ObserverController::class, 'ObserverCalendarViewPrivateEventDate'])->name('observer.calendar.viewprivateeventdate');
    Route::get('/manager/calendar/viewdepartmenteventdate', [ObserverController::class, 'ObserverCalendarViewDepartmentEventDate'])->name('observer.calendar.viewdepartmenteventdate');
    Route::post('/manager/calendar/removeevent', [ObserverController::class, 'ObserverCalendarRemoveEvent'])->name('observer.calendar.removeevent');
    Route::get('/manager/calendar/viewannouncementeventdate', [ObserverController::class, 'ObserverCalendarViewAnnouncementEventDate'])->name('observer.calendar.viewannouncementeventdate');

    //endregion

    Route::get('/manager/personal_table', [ObserverController::class, 'ObserverPersonalTable'])->name('observer.personal_table');
    Route::get('/manager/personal_table_sort', [ObserverController::class, 'ObserverPersonalTableSort'])->name('observer.personal_table_sort');
    Route::post('/manager/personal_table/mark_task', [ObserverController::class, 'ObserverPersonalTableMarkTask'])->name('observer.personal_table.mark_task');
    Route::post('/manager/personal_table/update_sort', [ObserverController::class, 'ObserverPersonalTableUpdateSort'])->name('observer.personal_table.update_sort');
    Route::post('/manager/personal_table/task_favorites', [ObserverController::class, 'ObserverPersonalTableTaskFavorites'])->name('observer.personal_table.task_favorites');
    Route::post('/manager/personal_table/task_important', [ObserverController::class, 'ObserverPersonalTableTaskImportant'])->name('observer.personal_table.task_important');
    Route::post('/manager/personal_table/task_tag', [ObserverController::class, 'ObserverPersonalTableTaskTag'])->name('observer.personal_table.task_tag');
    Route::post('/manager/personal_table/task_notes', [ObserverController::class, 'ObserverPersonalTableTaskNotes'])->name('observer.personal_table.task_notes');
    Route::post('/manager/personal_table/task_notes_remove', [ObserverController::class, 'ObserverPersonalTableTaskNotesRemove'])->name('observer.personal_table.task_notes_remove');
    Route::post('/manager/personal_table/notes_remove', [ObserverController::class, 'ObserverPersonalTableNotesRemove'])->name('observer.personal_table.notes_remove');
    Route::post('/manager/personal_table/edit_notes', [ObserverController::class, 'ObserverPersonalTableEditNotes'])->name('observer.personal_table.edit_notes');
    Route::post('/manager/personal_table/create_note', [ObserverController::class, 'ObserverPersonalTableCreateNotes'])->name('observer.personal_table.create_note');

    Route::get('/manager/logout', [ObserverController::class, 'ObserverLogout'])->name('observer.logout');
}); // End Group Observer Middler

Route::middleware(['auth','role:employee'])->group(function() {
    //region All Get
    Route::get('/employee/getallnotes', [EmployeeController::class, 'EmployeeDashboardGetAllNotes'])->name('employee.getallnotes');
    Route::get('/employee/getallchats', [EmployeeController::class, 'EmployeeDashboardGetAllChats'])->name('employee.getallchats');
    Route::get('/employee/getallfeedback', [EmployeeController::class, 'EmployeeDashboardGetAllFeedback'])->name('employee.getallfeedback');
    Route::get('/employee/getallnotification', [EmployeeController::class, 'EmployeeDashboardGetAllNotification'])->name('employee.getallnotification');
    Route::post('/employee/markasreadednotification', [EmployeeController::class, 'EmployeeDashboardMarkAsReadedNotification'])->name('employee.markasreadednotification');
    Route::post('/employee/clearnotification', [EmployeeController::class, 'EmployeeDashboardClearNotification'])->name('employee.clearnotification');
    Route::post('/employee/setreviewedfeed', [EmployeeController::class, 'EmployeeDashboardSetReviewedFeed'])->name('employee.setreviewedfeed');
    Route::post('/employee/removefeed', [EmployeeController::class, 'EmployeeDashboardRemoveFeed'])->name('employee.removefeed');
    //endregion

    Route::get('/employee/dashboard', [EmployeeController::class, 'EmployeeDashboard'])->name('employee.dashboard');
    Route::post('/employee/tasks/reload-myongoing-div', [EmployeeController::class, 'reloadMyOngoingDiv'])->name('reloademp.myongoing.div');

    Route::get('/employee/profile', [EmployeeController::class, 'EmployeeProfile'])->name('employee.profile');
    Route::post('/employee/profile/photo', [EmployeeController::class, 'SavePhoto'])->name('employee.profile.photo');
    Route::post('/employee/profile/pinfo', [EmployeeController::class, 'ProfileInfo'])->name('employee.profile.pinfo');
    Route::post('/employee/profile/binfo', [EmployeeController::class, 'BasicInfo'])->name('employee.profile.binfo');
    Route::post('/employee/profile/update', [EmployeeController::class, 'UpdatePassword'])->name('employee.profile.update');

    //region Personal Tabl
    Route::get('/employee/personal_table', [EmployeeController::class, 'EmployeePersonalTable'])->name('employee.personal_table');
    Route::get('/employee/personal_table_sort', [EmployeeController::class, 'EmployeePersonalTableSort'])->name('employee.personal_table_sort');
    Route::post('/employee/personal_table/mark_task', [EmployeeController::class, 'EmployeePersonalTableMarkTask'])->name('employee.personal_table.mark_task');
    Route::post('/employee/personal_table/update_sort', [EmployeeController::class, 'EmployeePersonalTableUpdateSort'])->name('employee.personal_table.update_sort');
    Route::post('/employee/personal_table/task_favorites', [EmployeeController::class, 'EmployeePersonalTableTaskFavorites'])->name('employee.personal_table.task_favorites');
    Route::post('/employee/personal_table/task_important', [EmployeeController::class, 'EmployeePersonalTableTaskImportant'])->name('employee.personal_table.task_important');
    Route::post('/employee/personal_table/task_tag', [EmployeeController::class, 'EmployeePersonalTableTaskTag'])->name('employee.personal_table.task_tag');
    Route::post('/employee/personal_table/task_notes', [EmployeeController::class, 'EmployeePersonalTableTaskNotes'])->name('employee.personal_table.task_notes');
    Route::post('/employee/personal_table/task_notes_remove', [EmployeeController::class, 'EmployeePersonalTableTaskNotesRemove'])->name('employee.personal_table.task_notes_remove');
    Route::post('/employee/personal_table/notes_remove', [EmployeeController::class, 'EmployeePersonalTableNotesRemove'])->name('employee.personal_table.notes_remove');
    Route::post('/employee/personal_table/edit_notes', [EmployeeController::class, 'EmployeePersonalTableEditNotes'])->name('employee.personal_table.edit_notes');
    Route::post('/employee/personal_table/create_note', [EmployeeController::class, 'EmployeePersonalTableCreateNotes'])->name('employee.personal_table.create_note');
    //endregion

    //region Task
    Route::get('/employee/tasks', [EmployeeController::class, 'EmployeeTasks'])->name('employee.tasks');
    Route::post('/employee/tasks/reload-ongoing-div', [EmployeeController::class, 'reloadOngoingDiv'])->name('reloademp.ongoing.div');
    Route::post('/employee/tasks/reload-tocheck-div', [EmployeeController::class, 'reloadToCheckDiv'])->name('reloademp.tocheck.div');
    Route::post('/employee/tasks/reload-complete-div', [EmployeeController::class, 'reloadCompleteDiv'])->name('reloademp.complete.div');
    Route::get('/employee/tasks/checklinktemp', [EmployeeController::class, 'EmployeeTasksCheckLinkTemp'])->name('employee.tasks.checklinktemp');
    Route::post('/employee/tasks/requestovertimetask', [EmployeeController::class, 'EmployeeTasksRequestOvertimeTask'])->name('employee.tasks.requestovertimetask');

    Route::get('/employee/etasks/{task}', [EmployeeController::class, 'EmployeeEditTasks'])->name('employee.etasks');
    Route::post('/employee/etasks/save', [EmployeeController::class, 'EmployeeEditSaveTasks'])->name('employee.etasks.save');
    Route::post('/employee/etasks/userstatuschecking', [EmployeeController::class, 'EmployeeEditUserStatusCheckingTasks'])->name('employee.etasks.userstatuschecking');

    Route::get('/employee/lvtasks/{task}', [EmployeeController::class, 'EmployeeLiveViewTasks'])->name('employee.lvtasks');
    Route::get('/employee/tasks/glvtasks', [EmployeeController::class, 'EmployeeGetLiveViewTasks'])->name('employee.tasks.glvtasks');
    Route::get('/employee/tasks/getradio', [EmployeeController::class, 'EmployeeTasksGetRadio'])->name('employee.tasks.getradio');
    Route::get('/employee/tasks/getdown', [EmployeeController::class, 'EmployeeTasksGetDown'])->name('employee.tasks.getdown');
    Route::post('/employee/tasks/livereloading', [EmployeeController::class, 'EmployeeTasksLiveReloading'])->name('employee.tasks.livereloading');

    Route::get('/employee/ptasks/{task}', [EmployeeController::class, 'EmployeePrintTasks'])->name('employee.ptasks');
    Route::get('/employee/tasks/gptasks', [EmployeeController::class, 'EmployeeGetPrintTasks'])->name('employee.tasks.gptasks');
    Route::get('/employee/tasks/printgetradio', [EmployeeController::class, 'EmployeeTasksPrintGetRadio'])->name('employee.tasks.printgetradio');
    Route::get('/employee/tasks/printgetdown', [EmployeeController::class, 'EmployeeTasksPrintGetDown'])->name('employee.tasks.printgetdown');
    Route::post('/employee/tasks/liveprintreloading', [EmployeeController::class, 'EmployeeTasksLivePrintReloading'])->name('employee.tasks.liveprintreloading');

    //endregion

    //region Calendar

    Route::get('/employee/calendar', [EmployeeController::class, 'EmployeeCalendar'])->name('employee.calendar');
    Route::get('/employee/calendar/viewtaskdate', [EmployeeController::class, 'EmployeeCalendarViewTaskDate'])->name('employee.calendar.viewtaskdate');
    Route::post('/employee/calendar/saveevent', [EmployeeController::class, 'EmployeeCalendarSaveEvent'])->name('employee.calendar.saveevent');
    Route::get('/employee/calendar/viewprivateeventdate', [EmployeeController::class, 'EmployeeCalendarViewPrivateEventDate'])->name('employee.calendar.viewprivateeventdate');
    Route::get('/employee/calendar/viewdepartmenteventdate', [EmployeeController::class, 'EmployeeCalendarViewDepartmentEventDate'])->name('employee.calendar.viewdepartmenteventdate');
    Route::post('/employee/calendar/removeevent', [EmployeeController::class, 'EmployeeCalendarRemoveEvent'])->name('employee.calendar.removeevent');
    Route::get('/employee/calendar/viewannouncementeventdate', [EmployeeController::class, 'EmployeeCalendarViewAnnouncementEventDate'])->name('employee.calendar.viewannouncementeventdate');

    //endregion

    Route::get('/employee/department', [EmployeeController::class, 'EmployeeDepartment'])->name('employee.department');

    //region Chat
    Route::get('/employee/chat', [EmployeeController::class, 'EmployeeChat'])->name('employee.chat');
    Route::post('/employee/chat/sendcontactmessage', [EmployeeController::class, 'EmployeeChatSendContactMessage'])->name('employee.chat.sendcontactmessage');
    Route::post('/employee/chat/reload-chat-list', [EmployeeController::class, 'reloadChatList'])->name('reloademp.chat.list');
    Route::get('/employee/chat/viewchats', [EmployeeController::class, 'EmployeeViewChats'])->name('employee.chat.viewchats');
    Route::post('/employee/chat/sendmessage', [EmployeeController::class, 'EmployeeChatSendMessage'])->name('employee.chat.sendmessage');
    Route::post('/employee/chat/reload-chat-message', [EmployeeController::class, 'reloadChatMessage'])->name('reloademp.chat.message');
    Route::get('/employee/chat/checkmessageattachment', [EmployeeController::class, 'EmployeeCheckMessageAttachment'])->name('employee.chat.checkmessageattachment');
    Route::get('/employee/chat/replieduser', [EmployeeController::class, 'EmployeeChatRepliedUser'])->name('employee.chat.replieduser');
    Route::get('/employee/chat/checkmessagereply', [EmployeeController::class, 'EmployeecheckMessageReply'])->name('employee.chat.checkmessagereply');
    Route::get('/employee/chat/geteditmessage', [EmployeeController::class, 'EmployeeGetEditMessage'])->name('employee.chat.geteditmessage');
    Route::post('/employee/chat/editmessage', [EmployeeController::class, 'EmployeeChatEditMessage'])->name('employee.chat.editmessage');
    Route::get('/employee/chat/vieweditmessage', [EmployeeController::class, 'EmployeeViewEditMessage'])->name('employee.chat.vieweditmessage');
    Route::get('/employee/chat/viewmessagecontact', [EmployeeController::class, 'EmployeeViewMessageContact'])->name('employee.chat.viewmessagecontact');
    Route::post('/employee/chat/sendforwardmessage', [EmployeeController::class, 'EmployeeChatSendForwardMessage'])->name('employee.chat.sendforwardmessage');
    Route::get('/employee/chat/checkwhoforward', [EmployeeController::class, 'EmployeeCheckWhoForward'])->name('employee.chat.checkwhoforward');
    Route::post('/employee/chat/unsendmessage', [EmployeeController::class, 'EmployeeChatUnsendMessage'])->name('employee.chat.unsendmessage');
    Route::post('/employee/chat/pinmessage', [EmployeeController::class, 'EmployeeChatPinMessage'])->name('employee.chat.pinmessage');
    Route::post('/employee/chat/unpinmessage', [EmployeeController::class, 'EmployeeChatUnpinMessage'])->name('employee.chat.unpinmessage');
    Route::post('/employee/chat/react', [EmployeeController::class, 'EmployeeChatReact'])->name('employee.chat.react');
    Route::post('/employee/chat/reload-chat-pinned', [EmployeeController::class, 'reloadChatPinned'])->name('reloademp.chat.pinned');
    Route::get('/employee/chat/viewpinnedmessage', [EmployeeController::class, 'EmployeeViewPinnedMessage'])->name('employee.chat.viewpinnedmessage');
    Route::post('/employee/chat/setnickname', [EmployeeController::class, 'EmployeeChatSetNickname'])->name('employee.chat.setnickname');
    Route::post('/employee/chat/setmutedchat', [EmployeeController::class, 'EmployeeChatSetMutedChat'])->name('employee.chat.setmutedchat');
    Route::post('/employee/chat/searchmessagevalue', [EmployeeController::class, 'EmployeeChatSearchMessageValue'])->name('employee.chat.searchmessagevalue');
    Route::get('/employee/chat/{chat}/media', [EmployeeController::class, 'getChatMedia'])->name('employee.chat.media');
    Route::post('/employee/chat/savemeeting', [EmployeeController::class, 'EmployeeChatSaveMeeting'])->name('employee.chat.savemeeting');
    Route::post('/employee/chat/removemeeting', [EmployeeController::class, 'EmployeeChatRemoveMeeting'])->name('employee.chat.removemeeting');
    Route::post('/employee/chat/creategroupmessage', [EmployeeController::class, 'EmployeeChatCreateGroupMessage'])->name('employee.chat.creategroupmessage');
    Route::post('/employee/chat/setconvoimage', [EmployeeController::class, 'EmployeeChatSetConvoImage'])->name('employee.chat.setconvoimage');
    Route::post('/employee/chat/unsetconvoimage', [EmployeeController::class, 'EmployeeChatUnsetConvoImage'])->name('employee.chat.unsetconvoimage');
    Route::post('/employee/chat/setconvoname', [EmployeeController::class, 'EmployeeChatSetConvoName'])->name('employee.chat.setconvoname');
    Route::post('/employee/chat/unsetconvoname', [EmployeeController::class, 'EmployeeChatUnsetConvoName'])->name('employee.chat.unsetconvoname');
    Route::post('/employee/chat/addnewmembergroup', [EmployeeController::class, 'EmployeeChatAddNewMemberGroup'])->name('employee.chat.addnewmembergroup');
    Route::post('/employee/chat/toggleasadmin', [EmployeeController::class, 'EmployeeChatToggleAsAdmin'])->name('employee.chat.toggleasadmin');
    Route::post('/employee/chat/removefromgroup', [EmployeeController::class, 'EmployeeChatRemoveFromGroup'])->name('employee.chat.removefromgroup');
    Route::post('/employee/chat/leaveconversation', [EmployeeController::class, 'EmployeeChatLeaveConversation'])->name('employee.chat.leaveconversation');
    Route::get('/employee/chat/checkwhoseen', [EmployeeController::class, 'EmployeeChatCheckWhoSeen'])->name('employee.chat.checkwhoseen');
    Route::post('/employee/chat/unsetchatishere', [EmployeeController::class, 'EmployeeChatUnsetChatIsHere'])->name('employee.chat.unsetchatishere');
    Route::post('/employee/chat/setchatishere', [EmployeeController::class, 'EmployeeChatSetChatIsHere'])->name('employee.chat.setchatishere');
    Route::post('/employee/chat/removeishere', [EmployeeController::class, 'EmployeeChatRemoveIsHere'])->name('employee.chat.removeishere');
    Route::get('/employee/chat/gettaskinfo', [EmployeeController::class, 'EmployeeChatGetTaskInfo'])->name('employee.chat.gettaskinfo');
    //endregion


    Route::get('/employee/log', [EmployeeController::class, 'EmployeeSystemLog'])->name('employee.log');

    Route::get('/employee/logout', [EmployeeController::class, 'EmployeeLogout'])->name('employee.logout');
});

Route::middleware(['auth','role:intern'])->group(function() {
    //region All Get
    Route::get('/intern/getallnotes', [InternController::class, 'InternDashboardGetAllNotes'])->name('intern.getallnotes');
    Route::get('/intern/getallchats', [InternController::class, 'InternDashboardGetAllChats'])->name('intern.getallchats');
    Route::get('/intern/getallfeedback', [InternController::class, 'InternDashboardGetAllFeedback'])->name('intern.getallfeedback');
    Route::get('/intern/getallnotification', [InternController::class, 'InternDashboardGetAllNotification'])->name('intern.getallnotification');
    Route::post('/intern/markasreadednotification', [InternController::class, 'InternDashboardMarkAsReadedNotification'])->name('intern.markasreadednotification');
    Route::post('/intern/clearnotification', [InternController::class, 'InternDashboardClearNotification'])->name('intern.clearnotification');
    Route::post('/intern/setreviewedfeed', [InternController::class, 'InternDashboardSetReviewedFeed'])->name('intern.setreviewedfeed');
    Route::post('/intern/removefeed', [InternController::class, 'InternDashboardRemoveFeed'])->name('intern.removefeed');
    //endregion

    Route::get('/intern/dashboard', [InternController::class, 'InternDashboard'])->name('intern.dashboard');
    Route::post('/intern/tasks/reload-myongoing-div', [InternController::class, 'reloadMyOngoingDiv'])->name('reloadint.myongoing.div');

    Route::get('/intern/profile', [InternController::class, 'InternProfile'])->name('intern.profile');
    Route::post('/intern/profile/photo', [InternController::class, 'SavePhoto'])->name('intern.profile.photo');
    Route::post('/intern/profile/pinfo', [InternController::class, 'ProfileInfo'])->name('intern.profile.pinfo');
    Route::post('/intern/profile/binfo', [InternController::class, 'BasicInfo'])->name('intern.profile.binfo');
    Route::post('/intern/profile/update', [InternController::class, 'UpdatePassword'])->name('intern.profile.update');

    //region Personal Tabl
    Route::get('/intern/personal_table', [InternController::class, 'InternPersonalTable'])->name('intern.personal_table');
    Route::get('/intern/personal_table_sort', [InternController::class, 'InternPersonalTableSort'])->name('intern.personal_table_sort');
    Route::post('/intern/personal_table/mark_task', [InternController::class, 'InternPersonalTableMarkTask'])->name('intern.personal_table.mark_task');
    Route::post('/intern/personal_table/update_sort', [InternController::class, 'InternPersonalTableUpdateSort'])->name('intern.personal_table.update_sort');
    Route::post('/intern/personal_table/task_favorites', [InternController::class, 'InternPersonalTableTaskFavorites'])->name('intern.personal_table.task_favorites');
    Route::post('/intern/personal_table/task_important', [InternController::class, 'InternPersonalTableTaskImportant'])->name('intern.personal_table.task_important');
    Route::post('/intern/personal_table/task_tag', [InternController::class, 'InternPersonalTableTaskTag'])->name('intern.personal_table.task_tag');
    Route::post('/intern/personal_table/task_notes', [InternController::class, 'InternPersonalTableTaskNotes'])->name('intern.personal_table.task_notes');
    Route::post('/intern/personal_table/task_notes_remove', [InternController::class, 'InternPersonalTableTaskNotesRemove'])->name('intern.personal_table.task_notes_remove');
    Route::post('/intern/personal_table/notes_remove', [InternController::class, 'InternPersonalTableNotesRemove'])->name('intern.personal_table.notes_remove');
    Route::post('/intern/personal_table/edit_notes', [InternController::class, 'InternPersonalTableEditNotes'])->name('intern.personal_table.edit_notes');
    Route::post('/intern/personal_table/create_note', [InternController::class, 'InternPersonalTableCreateNotes'])->name('intern.personal_table.create_note');
    //endregion

    //region Task
    Route::get('/intern/tasks', [InternController::class, 'InternTasks'])->name('intern.tasks');
    Route::post('/intern/tasks/reload-ongoing-div', [InternController::class, 'reloadOngoingDiv'])->name('reloadint.ongoing.div');
    Route::post('/intern/tasks/reload-tocheck-div', [InternController::class, 'reloadToCheckDiv'])->name('reloadint.tocheck.div');
    Route::post('/intern/tasks/reload-complete-div', [InternController::class, 'reloadCompleteDiv'])->name('reloadint.complete.div');
    Route::get('/intern/tasks/checklinktemp', [InternController::class, 'InternTasksCheckLinkTemp'])->name('intern.tasks.checklinktemp');
    Route::post('/intern/tasks/requestovertimetask', [InternController::class, 'InternTasksRequestOvertimeTask'])->name('intern.tasks.requestovertimetask');

    Route::get('/intern/etasks/{task}', [InternController::class, 'InternEditTasks'])->name('intern.etasks');
    Route::post('/intern/etasks/save', [InternController::class, 'InternEditSaveTasks'])->name('intern.etasks.save');
    Route::post('/intern/etasks/userstatuschecking', [InternController::class, 'InternEditUserStatusCheckingTasks'])->name('intern.etasks.userstatuschecking');

    Route::get('/intern/lvtasks/{task}', [InternController::class, 'InternLiveViewTasks'])->name('intern.lvtasks');
    Route::get('/intern/tasks/glvtasks', [InternController::class, 'InternGetLiveViewTasks'])->name('intern.tasks.glvtasks');
    Route::get('/intern/tasks/getradio', [InternController::class, 'InternTasksGetRadio'])->name('intern.tasks.getradio');
    Route::get('/intern/tasks/getdown', [InternController::class, 'InternTasksGetDown'])->name('intern.tasks.getdown');
    Route::post('/intern/tasks/livereloading', [InternController::class, 'InternTasksLiveReloading'])->name('intern.tasks.livereloading');

    Route::get('/intern/ptasks/{task}', [InternController::class, 'InternPrintTasks'])->name('intern.ptasks');
    Route::get('/intern/tasks/gptasks', [InternController::class, 'InternGetPrintTasks'])->name('intern.tasks.gptasks');
    Route::get('/intern/tasks/printgetradio', [InternController::class, 'InternTasksPrintGetRadio'])->name('intern.tasks.printgetradio');
    Route::get('/intern/tasks/printgetdown', [InternController::class, 'InternTasksPrintGetDown'])->name('intern.tasks.printgetdown');
    Route::post('/intern/tasks/liveprintreloading', [InternController::class, 'InternTasksLivePrintReloading'])->name('intern.tasks.liveprintreloading');

    //endregion

    //region Calendar

    Route::get('/intern/calendar', [InternController::class, 'InternCalendar'])->name('intern.calendar');
    Route::get('/intern/calendar/viewtaskdate', [InternController::class, 'InternCalendarViewTaskDate'])->name('intern.calendar.viewtaskdate');
    Route::post('/intern/calendar/saveevent', [InternController::class, 'InternCalendarSaveEvent'])->name('intern.calendar.saveevent');
    Route::get('/intern/calendar/viewprivateeventdate', [InternController::class, 'InternCalendarViewPrivateEventDate'])->name('intern.calendar.viewprivateeventdate');
    Route::get('/intern/calendar/viewdepartmenteventdate', [InternController::class, 'InternCalendarViewDepartmentEventDate'])->name('intern.calendar.viewdepartmenteventdate');
    Route::post('/intern/calendar/removeevent', [InternController::class, 'InternCalendarRemoveEvent'])->name('intern.calendar.removeevent');
    Route::get('/intern/calendar/viewannouncementeventdate', [InternController::class, 'InternCalendarViewAnnouncementEventDate'])->name('intern.calendar.viewannouncementeventdate');

    //endregion

    Route::get('/intern/department', [InternController::class, 'InternDepartment'])->name('intern.department');

    //region Chat
    Route::get('/intern/chat', [InternController::class, 'InternChat'])->name('intern.chat');
    Route::post('/intern/chat/sendcontactmessage', [InternController::class, 'InternChatSendContactMessage'])->name('intern.chat.sendcontactmessage');
    Route::post('/intern/chat/reload-chat-list', [InternController::class, 'reloadChatList'])->name('reloadint.chat.list');
    Route::get('/intern/chat/viewchats', [InternController::class, 'InternViewChats'])->name('intern.chat.viewchats');
    Route::post('/intern/chat/sendmessage', [InternController::class, 'InternChatSendMessage'])->name('intern.chat.sendmessage');
    Route::post('/intern/chat/reload-chat-message', [InternController::class, 'reloadChatMessage'])->name('reloadint.chat.message');
    Route::get('/intern/chat/checkmessageattachment', [InternController::class, 'InternCheckMessageAttachment'])->name('intern.chat.checkmessageattachment');
    Route::get('/intern/chat/replieduser', [InternController::class, 'InternChatRepliedUser'])->name('intern.chat.replieduser');
    Route::get('/intern/chat/checkmessagereply', [InternController::class, 'InterncheckMessageReply'])->name('intern.chat.checkmessagereply');
    Route::get('/intern/chat/geteditmessage', [InternController::class, 'InternGetEditMessage'])->name('intern.chat.geteditmessage');
    Route::post('/intern/chat/editmessage', [InternController::class, 'InternChatEditMessage'])->name('intern.chat.editmessage');
    Route::get('/intern/chat/vieweditmessage', [InternController::class, 'InternViewEditMessage'])->name('intern.chat.vieweditmessage');
    Route::get('/intern/chat/viewmessagecontact', [InternController::class, 'InternViewMessageContact'])->name('intern.chat.viewmessagecontact');
    Route::post('/intern/chat/sendforwardmessage', [InternController::class, 'InternChatSendForwardMessage'])->name('intern.chat.sendforwardmessage');
    Route::get('/intern/chat/checkwhoforward', [InternController::class, 'InternCheckWhoForward'])->name('intern.chat.checkwhoforward');
    Route::post('/intern/chat/unsendmessage', [InternController::class, 'InternChatUnsendMessage'])->name('intern.chat.unsendmessage');
    Route::post('/intern/chat/pinmessage', [InternController::class, 'InternChatPinMessage'])->name('intern.chat.pinmessage');
    Route::post('/intern/chat/unpinmessage', [InternController::class, 'InternChatUnpinMessage'])->name('intern.chat.unpinmessage');
    Route::post('/intern/chat/react', [InternController::class, 'InternChatReact'])->name('intern.chat.react');
    Route::post('/intern/chat/reload-chat-pinned', [InternController::class, 'reloadChatPinned'])->name('reloadint.chat.pinned');
    Route::get('/intern/chat/viewpinnedmessage', [InternController::class, 'InternViewPinnedMessage'])->name('intern.chat.viewpinnedmessage');
    Route::post('/intern/chat/setnickname', [InternController::class, 'InternChatSetNickname'])->name('intern.chat.setnickname');
    Route::post('/intern/chat/setmutedchat', [InternController::class, 'InternChatSetMutedChat'])->name('intern.chat.setmutedchat');
    Route::post('/intern/chat/searchmessagevalue', [InternController::class, 'InternChatSearchMessageValue'])->name('intern.chat.searchmessagevalue');
    Route::get('/intern/chat/{chat}/media', [InternController::class, 'getChatMedia'])->name('intern.chat.media');
    Route::post('/intern/chat/savemeeting', [InternController::class, 'InternChatSaveMeeting'])->name('intern.chat.savemeeting');
    Route::post('/intern/chat/removemeeting', [InternController::class, 'InternChatRemoveMeeting'])->name('intern.chat.removemeeting');
    Route::post('/intern/chat/creategroupmessage', [InternController::class, 'InternChatCreateGroupMessage'])->name('intern.chat.creategroupmessage');
    Route::post('/intern/chat/setconvoimage', [InternController::class, 'InternChatSetConvoImage'])->name('intern.chat.setconvoimage');
    Route::post('/intern/chat/unsetconvoimage', [InternController::class, 'InternChatUnsetConvoImage'])->name('intern.chat.unsetconvoimage');
    Route::post('/intern/chat/setconvoname', [InternController::class, 'InternChatSetConvoName'])->name('intern.chat.setconvoname');
    Route::post('/intern/chat/unsetconvoname', [InternController::class, 'InternChatUnsetConvoName'])->name('intern.chat.unsetconvoname');
    Route::post('/intern/chat/addnewmembergroup', [InternController::class, 'InternChatAddNewMemberGroup'])->name('intern.chat.addnewmembergroup');
    Route::post('/intern/chat/toggleasadmin', [InternController::class, 'InternChatToggleAsAdmin'])->name('intern.chat.toggleasadmin');
    Route::post('/intern/chat/removefromgroup', [InternController::class, 'InternChatRemoveFromGroup'])->name('intern.chat.removefromgroup');
    Route::post('/intern/chat/leaveconversation', [InternController::class, 'InternChatLeaveConversation'])->name('intern.chat.leaveconversation');
    Route::get('/intern/chat/checkwhoseen', [InternController::class, 'InternChatCheckWhoSeen'])->name('intern.chat.checkwhoseen');
    Route::post('/intern/chat/unsetchatishere', [InternController::class, 'InternChatUnsetChatIsHere'])->name('intern.chat.unsetchatishere');
    Route::post('/intern/chat/setchatishere', [InternController::class, 'InternChatSetChatIsHere'])->name('intern.chat.setchatishere');
    Route::post('/intern/chat/removeishere', [InternController::class, 'InternChatRemoveIsHere'])->name('intern.chat.removeishere');
    Route::get('/intern/chat/gettaskinfo', [InternController::class, 'InternChatGetTaskInfo'])->name('intern.chat.gettaskinfo');
    //endregion


    Route::get('/intern/log', [InternController::class, 'InternSystemLog'])->name('intern.log');

    Route::get('/intern/logout', [InternController::class, 'InternLogout'])->name('intern.logout');
});

Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');
Route::get('/head/login', [HeadController::class, 'HeadLogin'])->name('head.login');
Route::get('/manager/login', [ObserverController::class, 'ObserverLogin'])->name('observer.login');
Route::get('/user/login', [UserController::class, 'UserLogin'])->name('user.login');

Route::get('/admin/forgot', [AdminController::class, 'AdminForgot'])->name('admin.forgot');
Route::post('/admin/sendotpemail', [AdminController::class, 'AdminSendOtpEmail'])->name('admin.sendotpemail');
Route::post('/admin/submitnewpass', [AdminController::class, 'AdminSubmitNewPass'])->name('admin.submitnewpass');
Route::post('/admin/sendotpphone', [AdminController::class, 'AdminSendOtpPhone'])->name('admin.sendotpphone');

Route::get('/head/forgot', [HeadController::class, 'HeadForgot'])->name('head.forgot');
Route::post('/head/sendotpemail', [HeadController::class, 'HeadSendOtpEmail'])->name('head.sendotpemail');
Route::post('/head/submitnewpass', [HeadController::class, 'HeadSubmitNewPass'])->name('head.submitnewpass');
Route::post('/head/sendotpphone', [HeadController::class, 'HeadSendOtpPhone'])->name('head.sendotpphone');

Route::get('/manager/forgot', [ObserverController::class, 'ObserverForgot'])->name('observer.forgot');
Route::post('/manager/sendotpemail', [ObserverController::class, 'ObserverSendOtpEmail'])->name('observer.sendotpemail');
Route::post('/manager/submitnewpass', [ObserverController::class, 'ObserverSubmitNewPass'])->name('observer.submitnewpass');
Route::post('/manager/sendotpphone', [ObserverController::class, 'ObserverSendOtpPhone'])->name('observer.sendotpphone');

Route::get('/user/forgot', [UserController::class, 'UserForgot'])->name('user.forgot');
Route::post('/user/sendotpemail', [UserController::class, 'UserSendOtpEmail'])->name('user.sendotpemail');
Route::post('/user/submitnewpass', [UserController::class, 'UserSubmitNewPass'])->name('user.submitnewpass');
Route::post('/user/sendotpphone', [UserController::class, 'UserSendOtpPhone'])->name('user.sendotpphone');