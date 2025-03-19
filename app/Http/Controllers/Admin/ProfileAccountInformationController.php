<?php     

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\MobileVerifyController;
use App\MobileVerify;
use App\InternalUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mmeshkatian\Ariel\ActionContainer;
use Mmeshkatian\Ariel\FormBuilder;

class ProfileAccountInformationController extends Controller
{
    public function editProfile()
    {
        $internalUserId = auth('internal_users')->id();
        // dd($internalUser);
        $username = InternalUser::find($internalUserId);
        // dd($username);
        // dd($username->name);

        if($username->count() > 0){
            if($username->active != 1){
                throw ValidationException::withMessages([
                    'username' => [trans('web/auth.banned')],
                ]);
            }
            $name = $username->name;
        } else {
            throw ValidationException::withMessages([
                'username' => [trans('web/auth.banned')],
            ]);
        }

        
        $form = new FormBuilder(true,new ActionContainer('admin.ac.editprofile.store'));
        $form->addField('name',__('web/account.onlyAdminName'))->setType('text')->setValue($name);
        $form->addField('old_password',__('web/account.old_password'))->setType('password');
        $form->addField('new_password',__('web/account.new_password'))->setType('password')->rules('min:8');
        $form->addField('new_password_confirmation',__('web/account.new_password_confirmation'))->setType('password');

        return $form->render()->with('title', 'Change Profile');
    }

    public function editProfileStore(Request $request)
    {
        $internalUserId = auth('internal_users')->id();
        $internalUser = InternalUser::find($internalUserId);
        $editOp = "";
        // dd($request->input('old_password'));
        // dd($request->input('new_password'));
       

        if ($request->input('name') != $internalUser->name && !empty($request->input('name'))) {
            $internalUser->name = $request->input('name');
            $editOp = 'NameUpdate';
            $flashMsg = 'web/account.profile_changed_success';
        }

        if ($request->input('old_password') != null && $request->input('new_password') != null
                && !empty($request->input('new_password')) ) {
            $flashMsg = 'web/account.password_changed_success';
            $editOp = $editOp == '' ? 'PasswordUpdate' : 'All';
        }

        if ( $editOp == "PasswordUpdate" ||  $editOp == "All") {
            if(!Hash::check($request->input('old_password'),$internalUser->password))
                throw ValidationException::withMessages([
                    'old_password'=>[__('web/account.old_password_match')]
                ]);
            
            if($request->input('new_password') != $request->input('new_password_confirmation')) {
                throw ValidationException::withMessages([
                    'old_password'=>[__('web/account.new_password_confirm_error')]
                ]);
            }

            $request->validate([
                'new_password'=>[
                    'required',
                    'string',
                    'min:8',             // must be at least 8 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                    'confirmed'
                ],
            ],
            [
                'new_password.regex'=> __('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.')
            ]);
        
            $internalUser->password = bcrypt($request->input('new_password'));
        }

        if ($editOp != "") {
            $internalUser->save();
            return redirect()->back()->with("success_alert",__($flashMsg));
        } else {
            return redirect()->back()->with("danger_alert",__('web/account.no_data_change'));
        }
    }
   
}
