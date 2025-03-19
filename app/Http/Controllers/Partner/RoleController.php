<?php     

namespace App\Http\Controllers\Partner;

use App\Helpers;
use App\Helpers\Helper;
use App\InternalUser;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;
use Spatie\Permission\Models\Permission;



class RoleController extends BaseController
{
    private function routeName($route)
    {
        $routeName = explode(".",$route);
        $access = '';
        switch ($routeName[2] ?? ''){
            case 'index' :
                $access = "Show  {$routeName[1]} list";
            break;
            case 'create' :
                $access = "Create a new  {$routeName[1]}";
            break;
            case 'show' :
                $access = "View detail of each {$routeName[1]}";
            break;
            case 'edit' :
                $access = "Edit each {$routeName[1]}";
            break;
            case 'destroy':
                $access = "Remove {$routeName[1]}";
            break;
            default :
                $access = "{$routeName[2]} {$routeName[1]}";

        }
        return $access;
    }

    public function configure()
    {
        $this->model = Role::class;
        $this->setTitle("Role");
//        $this->addBladeSetting("side",true);
        $user = auth('partner')->user();
        $partner = $user->partner;
        $this->addQueryCondition("where",["name","like","{$partner->code} - %"]);
        $this->addColumn("Name", 'name');

        $this->addField("name", "Title", 'required|unique:roles,name');
        $this->addAction('partner.Role.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid']);
        $this->addAction('partner.Role.destroy','<i class="feather icon-trash-2"></i>','Remove',['$uuid'],null,['class'=>'ask']);
        $routeCollection = \Route::getRoutes();


        foreach ($routeCollection as $value) {

            if(!empty($value->getName()) && ($value->getAction()['namespace'] ?? '') == 'App\Http\Controllers\Partner' && !in_array($value->getAction()['as'] ?? '',config('static.allowed_routes'))) {
                if(\Ariel::exists($value->getName(),".store") || \Ariel::exists($value->getName(),".update"))
                    continue;
                $name = str_replace(".","___",$value->getName());

                $this->addField( 'permission['.$name.']',$this->routeName($value->getName()), '', 'checkbox',function ($data,$valuee) use($value){
                    if(!empty($valuee)){
                        $role = Role::findByName($valuee->name,'partner');
                        try {
                            if ($valuee->name == 'SuperAdmin' || $role->hasPermissionTo($value->getName()))
                                return "1";
                        }catch (\Exception $e){
                            return "0";
                        }
                    }
                });
            }
        }


        return $this;

    }

    public function store(Request $request,$data=null,$return = false)
    {
        $role_name = $request->input('name');
        $user = auth('partner')->user();
        $partner = $user->partner;
        $role_name = str_replace($partner->code,'',$role_name);
        $role_name = "{$partner->code} - {$role_name}";
        if(!empty($data)){
            $data = Role::where("id",$data->id);


            if($data->count() == 0)
                return abort(404);
            $role = $data->get()->first();
            $role = Role::findById($role->id,'partner');
            $role->name = $role_name;
            $role->save();
        }else{
            $role = Role::findOrCreate($role_name,'partner');
        }
        $permissions = $role->permissions()->get();
        if($permissions->count() > 0)
            foreach ($permissions as $permission) {
            $role->revokePermissionTo($permission);
            }
        foreach ((array) $request->input('permission') ?? [] as $perm=>$val) {
            if($val == '1'){
                $prm = Permission::findOrCreate(str_replace("___",".",$perm),'partner');
                $role->givePermissionTo($prm);
            }
        }
        return redirect()->route('partner.Role.index');

    }

    public function destroy(Request $request,$id)
    {
        try {
            if (is_numeric($id))
                $data = Role::where("id", $id)->withTrashed();
            else
                $data = Role::where("uuid", $id)->withTrashed();

            $data = $data->get()->first();
            $id = $data->id ?? 0;

//            $role = Role::findById($id, 'partner');
            if (InternalUser::role($data->name)->count() == 0) {
                parent::destroy($request,$id);
                return redirect()->route('Role.index')->with(config('ariel.success_alert'), trans('ariel::ariel.success_text'));

            } else {
                return redirect()->back()->with(config('ariel.danger_alert'), "Role is in use ");
            }
        }catch (\Exception $e){
            return redirect()->back()->with(config('ariel.danger_alert'),$e->getMessage());
        }
    }

}
