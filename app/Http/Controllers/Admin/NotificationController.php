<?php     

namespace App\Http\Controllers\Admin;

use App\Helpers;
use App\Helpers\Helper;
use App\Industry;
use App\InternalUser;
use App\Notification;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;


class NotificationController extends BaseController
{
    public function create($data = null)
    {
        $breadcrumbs = [
            ['name'=>'Admin Area','link'=>route('admin.dashboard.main')],
            ['name'=>'Notification','link'=>url()->current()],
            ['name'=>'Send New Notification','link'=>url()->current()],
        ];
        return view('admin.notification.notification',compact('breadcrumbs'));
    }
    public function store(Request $request, $data = null, $return = false)
    {
        $this->validate($request,[
            'title'=>'required',
            'msg'=>'required'
        ]);
        $users = User::get();
        foreach ($users as $user) {
            $user->sendNotification(strip_tags($request->input('title')), strip_tags($request->input('msg')), []);
        }
        return redirect()->back()->with('success','Notifications Sent Successfully');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            ['name'=>'Admin Area','link'=>route('admin.dashboard.main')],
            ['name'=>'Notification','link'=>url()->current()],
        ];
        return view('admin.notification.notification-list',compact('breadcrumbs'));
    }

    public function show($uuid)
    {
        $breadcrumbs = [
            ['name'=>'Admin Area','link'=>route('admin.dashboard.main')],
            ['name'=>'Notification','link'=>route('admin.Notification.index')],
            ['name'=>'Notification Details','link'=>''],
        ];
        $notification = Notification::whereUuid($uuid)->first();
        return view('admin.notification.notification-detail',compact('notification','breadcrumbs'));
    }


}
