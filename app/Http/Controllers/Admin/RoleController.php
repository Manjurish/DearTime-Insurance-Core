<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Helpers;
use App\Helpers\Helper;
use App\InternalUser;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;
use Spatie\Permission\Models\Permission;
use DataTables;



class RoleController extends ArielController
{
    private function routeName($route)
    {
        $routeName = explode(".", $route);

        $access = '';
        switch ($routeName[1] ?? '') {
            case 'index':
                $access = "Show  {$routeName[0]} list";
                break;
            case 'create':
                $access = "Create a new  {$routeName[0]}";
                break;
            case 'show':
                $access = "View detail of each {$routeName[0]}";
                break;
            case 'edit':
                $access = "Edit each {$routeName[0]}";
                break;
            case 'destroy':
                $access = "Remove {$routeName[0]}";
                break;
            default:
                $access = "{$routeName[1]} {$routeName[0]}";
        }

        return $access;
    }
    public function configure()
    {
        $this->model = Role::class;
        $this->setTitle("Role");
        $this->addBladeSetting("side", true);
        $this->addQueryCondition("where", ["id", "!=", "1"]);
        $this->addColumn("Name", 'name');

        $this->addField("name", "Title", 'required|unique:roles,name');
        $this->addAction('admin.Role.edit', '<i class="feather icon-edit-2"></i>', 'Edit', ['$uuid'], Helpers::getAccessControlMethod());
        $this->addAction('admin.Role.destroy', '<i class="feather icon-trash-2"></i>', 'Remove', ['$uuid'], Helpers::getAccessControlMethod(), ['class' => 'ask']);
        $routeCollection = \Route::getRoutes();


        foreach ($routeCollection as $value) {

            if (!empty($value->getName()) && ($value->getAction()['namespace'] ?? '') == 'App\Http\Controllers\Admin' && !in_array($value->getAction()['as'] ?? '', config('static.allowed_routes'))) {
                if (\Ariel::exists($value->getName(), ".store") || \Ariel::exists($value->getName(), ".update"))
                    continue;
                $name = str_replace(".", "___", $value->getName());

                $this->addField('permission[' . $name . ']', $this->routeName($value->getName()), '', 'checkbox', function ($data, $valuee) use ($value) {
                    if (!empty($valuee)) {
                        $role = Role::findByName($valuee->name, 'internal_users');
                        try {
                            if ($valuee->name == 'SuperAdmin' || $role->hasPermissionTo($value->getName()))
                                return "1";
                        } catch (\Exception $e) {
                            return "0";
                        }
                    }
                });
            }
        }

        return $this;
    }
    public function index(Request $request)
    {

        $this->getConfig();
        $my_columns = [];
        $cfg_cols =
        $this->get('cols');;
        $acts = $this->get('actions');
        $actions = [];
        $c = 1;
        // dd($cfg_cols);


        foreach ($cfg_cols as $mycol) {
            if ($mycol->value ?? NULL) {
                $c++;
                $my_columns[] = $mycol->value . ' as c_' . $c;
            }
        }

        // dd($my_columns);
        
        $act_c = 'c_' . ($c + 1);


        $cols_query = '';
        if (!empty($my_columns)) {
            $my_columns[] = 'uuid';
            $cols_query = DB::raw(implode(', ', $my_columns));
        } else {
            $cols_query = '*';
        }


        if ($request->ajax()) {
            // dd($request);
            $data = $this->model::select($cols_query)->latest()->get();
            // dd($data);
            $datatables = Datatables::of($data)
                ->addIndexColumn('c_1');
            if (!empty($acts)) {
                $datatables->addColumn(
                    $act_c,
                    function ($row) use ($acts) {
                        $btn = '';
                        foreach ($acts as $action) {
                            if (!$action->hasAccess($row)) {
                                continue;
                            }
                            if ($action->isGet()) {
                                $btn .= '<a data-toggle="tooltip" class="' . ($action->options['class'] ?? '') . ' data-placement="top" title="' . ($action->caption ?? '') . '" style="margin-left: 10px" href="' . $action->getUrl($row) . '">' . ($action->icon ?? '') . '</a>';
                            } else {
                                $btn .= '<form data-toggle="tooltip" data-placement="top" class="' . ($action->options['class'] ?? '') . '" title="' . ($action->caption ?? '') . '" style="margin-left: 10px;display: inline" method="post" action="' . $action->getUrl($row) . '"> ' . method_field($action->method) . csrf_field() . ($action->icon ?? '') . ' </form>';
                            }
                        }
                        return $btn;
                    }
                );
                $datatables->rawColumns([$act_c]);
            }
            $datatables->removeColumn('uuid');
            $datatables->make(true);
            // $datatables->make(true);
            return $datatables->toJson();
        } else {
            // dd($request->ajax());
            return parent::index($request);
        }
    }

    public function store(Request $request, $data = null, $return = false)
    {
        if (!empty($data)) {
            $data = Role::where("id", $data->id);


            if ($data->count() == 0)
                return abort(404);
            $role = $data->get()->first();
            $role = Role::findById($role->id, 'internal_users');
            $role->name = $request->input('name');
            $role->save();
        } else {
            $role = Role::findOrCreate($request->input('name'), 'internal_users');
        }
        $permissions = $role->permissions()->get();
        if ($permissions->count() > 0)
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        foreach ((array) $request->input('permission') ?? [] as $perm => $val) {
            if ($val == '1') {
                $prm = Permission::findOrCreate(str_replace("___", ".", $perm), 'internal_users');
                $role->givePermissionTo($prm);
            }
        }
        return redirect()->route('admin.Role.index');
    }

    public function destroy(Request $request, $id)
    {
        try {
            if (is_numeric($id))
                $data = Role::where("id", $id)->withTrashed();
            else
                $data = Role::where("uuid", $id)->withTrashed();

            $data = $data->get()->first();
            $id = $data->id ?? 0;

            //            $role = Role::findById($id, 'internal_users');
            if (InternalUser::role($data->name)->count() == 0) {
                parent::destroy($request, $id);
                return redirect()->route('admin.Role.index')->with(config('ariel.success_alert'), trans('ariel::ariel.success_text'));
            } else {
                return redirect()->back()->with(config('ariel.danger_alert'), "Role is in use ");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(config('ariel.danger_alert'), $e->getMessage());
        }
    }
}