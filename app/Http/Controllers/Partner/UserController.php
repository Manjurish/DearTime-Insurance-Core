<?php     

namespace App\Http\Controllers\Partner;

use App\Address;
use App\CharityApplicant;
use App\Company;
use App\CustomerVerification;
use App\Helpers;
use App\Helpers\Helper;
use App\Individual;
use App\Industry;
use App\InternalUser;
use App\PartnerUser;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Mmeshkatian\Ariel\BaseController;


class UserController extends BaseController
{

    public function configure()
    {
        $this->model = PartnerUser::class;
        $this->setTitle("User");
        $user = auth('partner')->user();
        $partner = $user->partner;
        $this->addQueryCondition("where",[["partner_id"=>$partner->id ?? 0]]);
        $this->addQueryCondition("where",["id","!=",$user->id]);

//        $this->addBladeSetting("side",true);
        $this->addColumn("Name",'name');
        $this->addColumn("Username",'username');
        $this->addColumn("Status",'activeToTEXT');

        $this->addField("name","Name",'required');
        $this->addField("username","Username",['required',Rule::unique('partner_users')->where("partner_id",$partner->id)]);
        $this->addField("password","Password",'required','password','',[],'savePassword',true,true);

        $this->addField("active","Status",'required','select','',['1'=>'Active','0'=>'Deactive']);
        $this->addField("role","Role",'required|exists:roles,id','select',
            function ($data,$value){
                try {
                    $data = !empty($value) ? (
                    Role::findByName($value->getRoleNames()[0] ?? '', 'partner')->id) : '';
                    return $data;
                }catch (\Exception $e){
                    return "";
                }
            },Role::where("guard_name","partner")->where("name",'like',"{$partner->code} - %")->get()->pluck("name","id")->toArray(),'saveUserRole',false,true);

        //userProfile

        $this->addAction('partner.User.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid']);
        $this->addAction('partner.User.destroy','<i class="feather icon-trash-2"></i>','Delete',['$uuid'],null,['class'=>'ask']);


        return $this;

    }

    public function savePassword($req,$val,$data)
    {
        $data->password = bcrypt($val);
        $data->save();

    }
public function saveUserRole($req,$val,$data)
    {

        $data->partner_id = auth('partner')->user()->partner_id;
        $data->save();

        $role = Role::find($val);
        $data->syncRoles([$role->name]);

    }

    public function resetPassword($id)
    {
        if(is_numeric($id))
            $data = PartnerUser::where("id",$id);
        else
            $data = PartnerUser::where("uuid",$id);

        $data = $data->get()->first();
        if(empty($data))
            abort(404);

        $user = $data;
        $token = Password::getRepository()->create($user);
        $user->sendPasswordResetNotification($token);
        //return redirect()->back()->with("success","Password reset link sent to user email address !");

    }

}
