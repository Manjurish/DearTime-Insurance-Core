<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\ActionContainer;
use Mmeshkatian\Ariel\FormBuilder;
use Mmeshkatian\Ariel\Router;
use Mmeshkatian\Ariel\BaseController;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Route;
use File;
use Config;

class ArielController extends BaseController
{

    public function index(Request $request)
    {

        $this->getConfig();

        $colNames = $this->get('colNames');
        $cols = $this->get('cols');
        $fields = $this->get('fields');
        $model = $this->get('model');
        $createRoute = $this->get('createRoute');
        $ListData = $this->get('ListData');
        $queryConditions = $this->get('queryConditions');
        $filters = $this->get('filters');
        $isSingleRow = $this->get('isSingleRow');
        $title = $this->get('title');
        $mainRoute = $this->get('mainRoute');

        // dd($cols);

        if ($isSingleRow) {
            $data = $model::get()->first();
            return $this->create($data);
        }

        if (!empty($ListData)) {
            $rows = $ListData;
        } else {
            $rows = $model::orderBy("created_at", "desc")->where("id", "!=", "N");
            foreach ($queryConditions as $queryCondition) {
                if (!empty($queryCondition['function'])) {
                    $function = $queryCondition['function'];

                    if (!empty($queryCondition['data'])) {
                        $rows = $rows->$function(...($queryCondition['data']));
                    } else {
                        $rows = $rows->$function();
                    }
                }
            }
            foreach ($filters as $filter) {
                $filter->handle($request, $rows);
            }
        }


        if ($request->input('trash') == '1') {

            try {
                $rows = $rows->onlyTrashed();
            } catch (\Exception $e) {
                $rows = $rows->where("id", "NN");
            }
            $this->actions = [];
            if (\Route::has($this->RoutePrefix . '.destroy')) {

                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-refresh-cw"></i>', 'Restore', ['$id', 'restore' => '1'], null, ['class' => 'ask']);
                $this->addAction($this->RoutePrefix . '.destroy', '<i class="feather icon-trash"></i>', 'Permanent Delete', ['$id', 'perm' => '1'], null, ['class' => 'ask']);
            }
        }
        $rows = $rows->get();


        // dd($rows);

        $breadcrumbs = [
            ['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
            ['name' => $title, 'link' => $mainRoute],
            ['name' => $title . ' List', 'link' => url()->current()],
        ];

        $saveRoute = $this->get('saveRoute');
        $data = null;
        $BladeSettings = $this->get('BladeSettings');

        $actions = $this->get('actions');
        $batchActions = $this->get('batchActions');

        $scripts = $this->get('scripts');
        $filters = $this->get('filters');
        $sections = $this->get('sections');
        foreach ($sections as $section)
            $section->compile(null);

        return view('ariel::index', compact('colNames', 'cols', 'fields', 'rows', 'createRoute', 'actions', 'mainRoute', 'saveRoute', 'data', 'BladeSettings', 'title', 'breadcrumbs', 'scripts', 'filters', 'sections', 'batchActions'));
    }

    public function create($data = null)
    {

        $this->getConfig();

        $id = $data->uuid ?? $data->id ?? null;
        if (!empty($data) && empty($id))
            $data = null;
        $fields = $this->get('fields');
        $mainRoute = $this->get('mainRoute');
        $parameters = request()->route()->parameters();
        $extraP = [$id];
        if (count($parameters) > 1) {
            $extraP[] = Arr::first($parameters);

            $extraP = array_reverse($extraP);
        }

        $saveRoute = empty($data) ? $this->get('saveRoute') : new ActionContainer($this->RoutePrefix . '.' . Router::UPDATE, '', '', $extraP);
        $BladeSettings = $this->get('BladeSettings');
        $breadcrumbs = [
            ['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
            ['name' => $this->get('title'), 'link' => $this->get('mainRoute')],
            ['name' => (empty($data) ? 'Create ' : 'Edit ') . $this->get('title'), 'link' => url()->current()],
        ];
        $title = (empty($data) ? 'Create ' : 'Edit ') . $this->get('title');
        $script = $this->get('scripts');
        $formBuilder = new FormBuilder(true, $saveRoute);
        $formBuilder->setFields($this->get('fields'));
        $sections = $this->get('sections');
        foreach ($sections as $section)
            $section->compile($data);

        return $formBuilder->render($data, $script, $breadcrumbs, $sections);
        //        return view('ariel::create',compact('fields','mainRoute','saveRoute','id','data','breadcrumbs','title','BladeSettings','script'));
    }
}