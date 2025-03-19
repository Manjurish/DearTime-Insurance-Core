<?php

namespace App\Http\Controllers\Api;


use App\Beneficiary;
use App\Coverage;
use App\CoverageOrder;
use App\Credit;
use App\Events\ChangedCoveragesStatusEvent;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\Controller;
use App\Individual;
use App\Jobs\GenerateDocument;
use App\Jobs\ProcessPayment;
use App\Notifications\Email;
use App\Order;
use App\Thanksgiving;
use App\Transaction;
use App\SpoCharityFundApplication;
use App\Underwriting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CorporateController extends Controller
{
    public function updateCoverageStatus(Request $request)
    {
        try {
            $user = \auth('web')->user();
            if (empty($user))
                $user = auth()->user();

            $user = $user->profile;
            $user_c = $user;

            
            if (!empty($request->input('covered_payer_id'))) {
                $coverages = $user->coverages_owner()->where('payer_id', '=', $request->input('covered_payer_id'))->whereIn('status', [Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID]);
                $coverages->update(['corporate_user_status' => 'accepted']);
            }
            
            return ['status' => 'success', 'next_page' => 'order_receipt_page', 'data' => ['next_page' => 'order_receipt_page']];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
