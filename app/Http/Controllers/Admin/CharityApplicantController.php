<?php

namespace App\Http\Controllers\Admin;

use App\CharityApplicant;
use App\Helpers;
use App\Helpers\Helper;
use App\Individual;
use App\Industry;
use App\IndustryJob;
use App\InternalUser;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;
use DB;
use DataTables;


class CharityApplicantController extends ArielController
{
    public function configure()
    {
        $this->model = CharityApplicant::class;
        //        $this->addBladeSetting("side",true);
        $this->setTitle("Charity Applicant");
        $this->addColumn("Name", 'individual.name');
        $this->addColumn("Number of Dependants", 'dependants');
        $this->addColumn("Status", function ($q) {
            if ($q->active == 0) return 'deactive';
            else return 'active';
        });

        $this->addField("individual_id", "User Name", 'required', 'select', '', Individual::get()->pluck("name", "id")->toArray());

        $this->addAction('admin.CharityApplicant.edit', '<i class="feather icon-edit-2"></i>', 'Edit', ['$uuid'], Helpers::getAccessControlMethod());
        $this->addAction('admin.CharityApplicant.destroy', '<i class="feather icon-trash-2"></i>', 'Delete', ['$uuid'], Helpers::getAccessControlMethod(), ['class' => 'ask']);
        $this->addBladeSetting('hideCreate', true);
        return $this;
    }

    public function index(Request $request)
    {

        $this->getConfig();
        $my_columns = [];
        $cfg_cols = $this->get('cols');
        $acts = $this->get('actions');
        $actions = [];
        $c = 1;
        $individual_id_col = '';


        foreach ($cfg_cols as $mycol) {
            if ($mycol->name == 'Status') {
                $c++;
                $my_columns[] = 'active as c_' . $c;
            } elseif ($mycol->value == 'individual.name') {
                $c++;
                $my_columns[] = 'individual_id as c_' . $c;
                $individual_id_col = 'c_' . $c;
            } else {
                if ($mycol->value ?? NULL) {
                    $c++;
                    $my_columns[] = ($mycol->value) . ' as c_' . $c;
                }
            }
        }


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
            if ($individual_id_col != '') {
                $datatables->editColumn($individual_id_col, function (Individual $individual) {
                    return $individual->name;
                });
            }
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

    public function details($uuid)
    {
        $user = User::where("uuid", $uuid)->get()->first();
        notFound(empty($user));
        notFound($user->type != 'individual');

        if (empty($user->profile)) {
            $individual = new Individual();
            $individual->user_id = $user->id;
            $individual->name = $user->name;
            $individual->mobile = '';
            $individual->save();
        }
        $user = User::where("uuid", $uuid)->get()->first();
        $charity = CharityApplicant::where("individual_id", $user->profile->id)->get()->first();
        if (empty($charity)) {
            $charity = new CharityApplicant();
            $charity->individual_id = $user->profile->id;
            $charity->save();
        }
        return redirect()->route('admin.CharityApplicant.edit', [$charity->uuid]);
    }
    public function store(Request $request, $data = null, $return = false)
    {
        if (!empty($request->input('individual_id')) && empty($data)) {
            $CharityApplicant = CharityApplicant::where("individual_id", $request->input('individual_id'))->get()->first();
            if (!empty($CharityApplicant))
                return redirect()->route('admin.CharityApplicant.edit', [$CharityApplicant->uuid]);
        }
        $store =  parent::store($request, $data, true);

        if ($store->wasRecentlyCreated)
            return redirect()->route('admin.CharityApplicant.edit', $store->uuid)->with("success", "Operation Successful");

        return redirect()->route('admin.CharityApplicant.index')->with("success", "Operation Successful");
    }

    public function edit($id)
    {
        $this->set("skipConfigureFields", true);
        $this->addField("individual_id", "", '', 'hidden', function ($c, $v) {
            return $v->individual_id;
        });

        if (is_numeric($id))
            $data = CharityApplicant::where("id", $id);
        else
            $data = CharityApplicant::where("uuid", $id);

        $data = $data->get()->first();
        notFound(empty($data));

        $this->fields($data);
        return parent::edit($id);
    }

    public function update(Request $request, $id)
    {
        if (is_numeric($id))
            $data = CharityApplicant::where("id", $id);
        else
            $data = CharityApplicant::where("uuid", $id);

        $data = $data->get()->first();
        notFound(empty($data));

        $this->fields($data);
        if ($request->input('active') != $data->active) {
            $user = $data->individual->user ?? null;
            if (!empty($user))
                $user->sendNotification(__('notification.change_charity_application_status.title'), __('notification.change_charity_application_status.body') . ($request->input('active') == 0 ? 'Deactive' : 'Active'), ['command' => 'next_page', 'data' => 'product_page']);
        }

        return parent::update($request, $id);
    }

    public function fields($data)
    {
        $this->addField("about_self", "About your self", 'required', 'text', '');
        $this->addField("sponsor_thank_note", "Sponsor thanks note", 'required', 'text', '');
        $this->addField("dependants", "Number of dependants", 'required', 'number', '');
        $this->addField("selfie", "Selfie", '', 'image', function ($c, $v) {
            if (!empty($v->documents)) {
                return $v->documents()->where("type", "selfie")->get()->first()->Link ?? '';
            }
        }, [], 'saveSelfie', true, true, true);
        $this->addField("images[]", "Monthly Household Income Prof", '', 'multiimage', function ($c, $v) {
            $out = [];
            if (!empty($v->documents)) {
                foreach ($v->documents()->where("type", "salary_proof")->get() as $doc) {
                    $out[] = $doc->Link;
                }
            }
            return $out;
        }, [], 'saveImages', true, true, true);
        $this->addField("active", "Status", 'required', 'select', '', ['1' => 'Active', '0' => 'Deactive']);
    }

    public function saveSelfie($request, $val, $data)
    {
        if ($request->has('selfie'))
            $selfie = Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $data, 'selfie');
    }

    public function saveImages($request, $val, $data)
    {
        $doc = [];
        foreach ($request->file('images') ?? [] as $file) {
            $doc[] = Helpers::crateDocumentFromUploadedFile($file, $data, 'salary_proof');
        }
    }
}