<?php     

namespace App\Http\Controllers\Admin;

use App\Helpers;
use App\Helpers\Helper;
use App\InternalUser;
use App\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;


class InternalUserController extends Controller
{
	public function index()
	{
		return view('admin.users.index');
	}

    public function configure()
    {
        $this->model = InternalUser::class;
        $this->setTitle("Users");
        $this->addBladeSetting("side",true);
        $this->addQueryCondition("where",["id","!=","1"]);
        $this->addColumn("Name",'name');
        $this->addColumn("Email",'email');
        $this->addColumn("Position",'position');
        $this->addColumn("Status",'activeToTEXT');
        $this->addColumn("Created",function ($q){
            return Carbon::parse($q->created_at)->format('d/m/Y H:i A');
        });


        $this->addField("name","Name",'required');
        $this->addField("email","Email",'required|email');
        $this->addField("password","Password",['store'=>'required'],'password','',[],'passwordMaker',true);
        $this->addField("position","Position",'required');
        $this->addField("role","Role",'required|exists:roles,id','select',
            function ($data,$value){
                try {
                    $data = !empty($value) ? (
                    Role::findByName($value->getRoleNames()[0] ?? '', 'internal_users')->id) : '';
                     return $data;
                }catch (\Exception $e){
                    return "";
                }
            },Role::where("guard_name","internal_users")->get()->pluck("name","id")->toArray(),'saveUserRole',false,true);
        $this->addField("active","Status",'required','select','',["1"=>"Active","0"=>"Disable"]);

        $this->addAction('admin.InternalUser.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.InternalUser.destroy','<i class="feather icon-trash-2"></i>','Delete',['$uuid'],Helpers::getAccessControlMethod(),['class'=>'ask']);
        $this->addBladeSetting('hideCreate',true);

        return $this;

    }



    public function passwordMaker($request,$val)
    {
        if(!empty($val))
            return bcrypt($val);
    }

    public function saveUserRole($req,$val,$data)
    {
        $role = Role::find($val);
        $data->syncRoles([$role->name]);

    }



}
