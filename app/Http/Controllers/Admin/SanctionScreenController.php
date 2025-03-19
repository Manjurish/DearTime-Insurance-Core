<?php

namespace App\Http\Controllers\Admin;

use App\Helpers;
use App\Industry;
use App\InternalUser;
use App\Role;
use App\User;
use App\UserScreening;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;
use DB;
use DataTables;


class SanctionScreenController extends ArielController
{
    public function configure()
    {
        $this->model = UserScreening::class;
        $this->addBladeSetting('hideCreate', true);

        $this->setTitle("Screening");
        $this->addBladeSetting("side", true);

        $userId = (int)\request()->input('user');
        if (!empty($userId)) {
            $this->addQueryCondition('where', ['user_id', $userId]);
        }

        $this->addColumn("Customer Ref Number", function ($q) {
            return $q->user->ref_no;
        });
        $this->addColumn("Customer Name", 'userName');
        $this->addColumn("Match Status", 'match_status');
        $this->addColumn("Status", 'status');
        $this->addColumn("Date", function ($q) {
            return Carbon::parse($q->created_at)->format('d/m/Y H:i A');
        });

        $this->addAction('admin.userScreen.edit', '<i class="feather icon-clipboard"></i>', 'Review', ['$id'], Helpers::getAccessControlMethod());
        $this->addAction('admin.User.show', '<i class="feather icon-user"></i>', 'Show User', ['$userUuid'], Helpers::getAccessControlMethod());
        $this->addAction('admin.userScreen.details', '<i class="feather icon-activity"></i>', 'Show Details', ['$id'], Helpers::getAccessControlMethod());

        return $this;
    }

    public function index(Request $request)
    {

        $this->getConfig();
        $my_columns = [];
        $cfg_cols = ['ref', 'name', 'match_status', 'status', 'created_at'];

        $acts = $this->get('actions');
        $actions = [];
        $c = 1;
        // dd($cfg_cols);


        foreach ($cfg_cols as $mycol) {
            if ($mycol ?? NULL) {
                $c++;
                $my_columns[] = $mycol . ' as c_' . $c;
            }
        }

        $act_c = 'c_' . ($c + 1);


        $cols_query = '';
        if (!empty($my_columns)) {
            $my_columns[] = 'id';
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
            $datatables->removeColumn('id');
            $datatables->make(true);
            // $datatables->make(true);
            return $datatables->toJson();
        } else {
            // dd($request->ajax());
            return parent::index($request);
        }
    }

    public function edit($id)
    {
        $data = UserScreening::findOrfail($id);
        return view('admin.screening.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $updated = UserScreening::whereId($input['id'])->update([
            'status' => $input['status']
        ]);
        return redirect()->back()->with($updated ? "success_alert" : "error_alert", $updated ? "Update Successful" : "Wrong At Update");
    }

    public function details($id)
    {
        $breadcrumbs = [
            ['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
            ['name' => 'Screening List', 'link' => route('admin.SanctionScreen.index')],
            ['name' => 'Screening Detail', 'link' => url()->current()],
        ];
        $screening = UserScreening::where('id', $id)->first();
        $data = Helpers::paginate($screening->details, 20);
        return view('admin.screening.details', compact('data', 'breadcrumbs'));
    }
}