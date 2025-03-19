<?php     

namespace App\Http\Controllers\Admin;

use App\CoverageModerationAction;
use App\Helpers\Enum;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\InternalUser;
use Illuminate\Http\Request;
use Mmeshkatian\Ariel\BaseController;


class CoverageModerationActionController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request,[
            'individual_id' => 'required',
        ]);

        $coverageModerationActions = CoverageModerationAction::select('action','remark','created_by','created_at')
            ->where('individual_id', $request->input('individual_id'))
            ->where('product_id', $request->input('product_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $table = $this->generateTable($coverageModerationActions);

        return response()->json([
            'table' => $table
        ]);
    }
    public function store(Request $request)
    {
        $this->validate($request,[
            'individual_id' => 'required',
            'product_id'    => 'required',
            'created_by'    => 'required',
            'action'        => 'required',
            'remark'        => 'nullable',
        ]);

        $coverageModerationAction = $this->saveCoverageModerationAction($request);

        $beforeAction = $request->input('action');
        $currentAction = '';
        $currentAction = $this->getCurrentAction($beforeAction);

        return response()->json([
            'individual_id'  => $coverageModerationAction->individual_id,
            'product_id'     => $coverageModerationAction->product_id,
            'created_by'     => $coverageModerationAction->created_by,
            'current_state'  => $beforeAction,
            'action'         => $currentAction,
        ]);

    }

    /**
     * @param $coverageModerationActions
     * @return string
     */
    private function generateTable($coverageModerationActions): string
    {
        if($coverageModerationActions->count() <= 0){
            $table = 'Without Log';
        }else{
            $table = '<div class="table-responsive"> <table id="table" class="display table table-data-width mx-auto w-auto"><tr><th>Action</th><th>Remark</th><th>Created By</th><th>Created At</th></tr>';
            foreach ($coverageModerationActions as $key => $val) {
                $table .= '<tr>';
                $table .= '<td>' . $val->action . '</td>';
                $table .= '<td style="white-space:pre; line-break: auto;">' . htmlspecialchars($val->remark) . '</td>';
                $table .= '<td>' . InternalUser::find($val->created_by)->name
                    . '</td>';
                $table .= '<td>' . $val->created_at . '</td>';
                $table .= '</tr>';
            }
            $table .= '</table></div>';
        }

        return $table;
    }

    /**
     * @param $beforeAction
     * @return string
     */
    private function getCurrentAction($beforeAction): string
    {
        if($beforeAction == Enum::COVERAGE_MODERATION_ACTION_ALLOW_PURCHASE){
            $currentAction = Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE;
        }elseif($beforeAction == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE){
            $currentAction = Enum::COVERAGE_MODERATION_ACTION_ALLOW_PURCHASE;
        }elseif($beforeAction == Enum::COVERAGE_MODERATION_ACTION_ALLOW_INCREASE){
            $currentAction = Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE;
        }elseif($beforeAction == Enum::COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE){
            $currentAction = Enum::COVERAGE_MODERATION_ACTION_ALLOW_INCREASE;
        }
        return $currentAction;
    }

    /**
     * @param Request $request
     * @return CoverageModerationAction
     */
    private function saveCoverageModerationAction(Request $request): CoverageModerationAction
    {
        $coverageModerationAction = new CoverageModerationAction;
        $coverageModerationAction->individual_id = $request->input('individual_id');
        $coverageModerationAction->product_id = $request->input('product_id');
        $coverageModerationAction->created_by = $request->input('created_by');
        $coverageModerationAction->remark = $request->input('remark');
        $coverageModerationAction->action = $request->input('action');
        $coverageModerationAction->save();
        return $coverageModerationAction;
    }
}
