<?php     

namespace App\Http\Controllers\Admin;

use App\InternalUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;

class ProfileController extends BaseController
{
    public function configure()
    {

        $this->model = InternalUser::class;
        $this->setTitle("Edit Profile");
        $this->set("isSingleRow",true);
        $this->addField("name","Name",'required');
        $this->addField("password","New Password",'','password','',[],'passwordMaker',true);
        $this->addField("confirm_new_password","Confirm New Password",'','password','',[],'',true,false,true);
        $this->addQueryCondition("where",["id",auth('internal_users')->id()]);
    }

    public function passwordMaker($request,$val)
    {
        if($request->input('confirm_new_password') != $request->input('password'))
            throw new \Exception("Password Confirmation is invalid");

        if(!empty($val))
            return bcrypt($val);
    }
}
