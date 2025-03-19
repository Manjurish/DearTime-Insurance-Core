<?php

namespace App\Http\Controllers\Admin;

use App\Action;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Individual;
use App\BankAccount;
use App\User;
use App\Uw;
use App\Order;
use App\Referral;
use App\CustomerReport;
use App\Beneficiary;
use App\Transaction;
use App\Underwriting;
use App\ViewIndividualsUw;
use App\Coverage;
use App\CustomerDetailsReport;
use App\CustomerVerification;
use App\CustomerVerificationDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
{
	public function index()
	{
		$minYearIndividual = $maxYearIndividual = $minYearUnderwriting = $maxYearUnderwriting = NULL;

		if (!empty(Individual::first())) {
			$minYearIndividual = Carbon::parse(Individual::select('created_at')->orderBy('created_at', 'asc')->first()->created_at)->year;
			$maxYearIndividual = Carbon::parse(Individual::select('created_at')->orderBy('created_at', 'desc')->first()->created_at)->year;
		}

		if (!empty(Underwriting::first())) {
			$minYearUnderwriting = Carbon::parse(Underwriting::select('created_at')->orderBy('created_at', 'asc')->first()->created_at)->year;
			$maxYearUnderwriting = Carbon::parse(Underwriting::select('created_at')->orderBy('created_at', 'desc')->first()->created_at)->year;
		}

		$breadcrumbs = [
			['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
			['name' => 'Reporting', 'link' => url()->current()],
		];

		return view('admin.reports.index', compact('breadcrumbs', 'minYearUnderwriting', 'maxYearUnderwriting', 'minYearIndividual', 'maxYearIndividual'));
	}

	public function insurancePenetration(Request $request)
	{
		$this->validate($request, [
			'start' => 'required',
		 ]);

		$selected      = $request->get('start');
		//strtotime($year);
		$month =Carbon::parse( $selected  )->format('m');
		$year =Carbon::parse( $selected  )->format('Y');

		$uninsured = [];
		$insured   = [];
		

		for ($j = 1; $j <= 12; $j++) {
			if($j == $month){
			$uninsured[$j] = Individual::where('has_other_life_insurance', 0)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();

			$insured[$j] = Individual::where('has_other_life_insurance', 1)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
			}else{
				$uninsured[$j]='';
				$insured[$j]='';
			}
		}
       
		//$pathFile    = resource_path('reporting/Penetration_Ratio_V1.0.xlsx');
		$pathFile    = resource_path('reporting/Penetration_Ratio.xlsx');
		$columnStart = 'c';
		$columnEnd   = 'n';

		$spreadsheet = IOFactory::load($pathFile);
		$sheet       = $spreadsheet->getActiveSheet();

		$index = 1;
		for ($i = $columnStart; $i <= $columnEnd; $i++) {
			$sheet->setCellValue($i . 10, $uninsured[$index]);
			$sheet->setCellValue($i . 11, $insured[$index]);
			$index++;
		}

		//$sheet->setCellValue('c5', $year);

		$filename = "{$year}-Insurance-Penetration-" . str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".xlsx";

		try {
			$writer = new Xlsx($spreadsheet);
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");
			$writer->save("php://output");
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}

	public function customerDetails(Request $request){

		// $results = DB::table('users')
		// ->distinct()
		// ->select(
		// 	'individuals.id As IndividualId' ,
		// 	'users.ref_no AS RefNo',
		// 	'individuals.name AS Name',
		// 	DB::raw("CASE
		// 		WHEN beneficiaries.id IS NOT NULL AND coverages.id IS NOT NULL THEN 'Owner, Beneficiary, DT-User'
		// 		WHEN beneficiaries.id IS NOT NULL THEN 'Beneficiary, DT-User'
		// 		WHEN coverages.id IS NOT NULL THEN 'Owner, DT-User'
		// 		WHEN users.password IS NOT NULL THEN 'DT-User'
		// 		WHEN users.password IS NULL THEN 'DT-User'
		// 		ELSE ''
		// 		END AS Type"
		// 	),
		// 	'individuals.nric AS Nric',
		// 	'individuals.dob AS DateOfBirth',
		// 	'individuals.gender AS Gender',
		// 	'industries.name AS Industry',
		// 	'industry_jobs.name AS Occupation',
		// 	'users.corporate_type AS CPFO'
		// )
		// ->leftJoin('beneficiaries', 'beneficiaries.email', '=', 'users.email')
		// ->leftJoin('individuals', 'individuals.user_id', '=', 'users.id')
		// ->leftJoin('coverages', 'coverages.covered_id', '=', 'individuals.id')
		// ->leftJoin('industry_jobs', 'industry_jobs.id', '=', 'individuals.occ')
		// ->leftJoin('industries', 'industries.id', '=', 'industry_jobs.industry_id')
		// ->where('users.type', '=', 'individual')
		// ->orderBy('users.ref_no', 'desc')
		// ->get();		


		$results = CustomerDetailsReport::all();
		

		// foreach ($results as $result) {
		//  $underwriting =	Underwriting::where('individual_id', $result->IndividualId)->first();

			
		//  if ($underwriting && isset($underwriting->answers['smoke'])) {
			
		// 	$result->smoke = $underwriting->answers['smoke'];  
			
		// } else {
		// 	$result->smoke = null; 
		// }

		
		// if(isset($underwriting) && $underwriting->death==1 && $underwriting->disability==1 && $underwriting->ci==1 && $underwriting->medical==1){
		// 	$result->status = "accepted";
		// } else {
		// 	$result->status = "rejected";
		// }

		// }

		// dd($results);

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
   

	$sheet->setCellValue('A1', 'Ref No');
	$sheet->setCellValue('B1', 'Name');
	$sheet->setCellValue('C1', 'Type');
	$sheet->setCellValue('D1', 'Nric');
	$sheet->setCellValue('E1', 'Date of Birth');
	$sheet->setCellValue('F1', 'Gender');
	$sheet->setCellValue('G1', 'Industry');
	$sheet->setCellValue('H1', 'Occupation');
	$sheet->setCellValue('I1', 'CPFO');
	$sheet->setCellValue('J1', 'Smoke');
	$sheet->setCellValue('K1', 'Status');
	
		  
	$row = 2;
	foreach ($results as $result) {
		$sheet->setCellValue('A' . $row, $result->{"Ref No"});
		$sheet->setCellValue('B' . $row, $result->Name);
		$sheet->setCellValue('C' . $row, $result->Type);
		$sheet->setCellValue('D' . $row, $result->Nric);
		$sheet->setCellValue('E' . $row, $result->DateOfBirth);
		$sheet->setCellValue('F' . $row, $result->Gender);
		$sheet->setCellValue('G' . $row, $result->Industry);
		$sheet->setCellValue('H' . $row, $result->Occupation);
		$sheet->setCellValue('I' . $row, $result->CPFO);
		
		$sheet->setCellValue('K' . $row, $result->status);

	
			if (is_null($result->getAttributes()['Answers'])) {
				$sheet->setCellValue('J' . $row, 'null');
			} else {
				$sheet->setCellValue('J' . $row, $result->Answers['smoke'] ?? '');
			}
		
		$row++;
	}


	$writer = new Xlsx($spreadsheet);
	
	
	header('X-Vapor-Base64-Encode:true');
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="customer_Details.xlsx"');
	// header('Cache-Control: max-age=0');
	

	$writer->save('php://output');
	// exit();



	}

	public function underwritingRejection(Request $request)
	{
		 $this->validate($request, [
			'start' => 'required',
		 ]);

		$selected      = $request->get('start');
		//strtotime($year);
		$month =Carbon::parse( $selected  )->format('m');
		$year =Carbon::parse( $selected  )->format('Y');

		
		$rejected = [];
		$accepted = [];

		for ($j = 1; $j <= 12; $j++) {
			if($j == $month){

			$rejected[$j] = Underwriting::where('death', 0)
			   ->Where('disability',0)
			   ->Where('ci',0)
			   ->Where('medical',0)
				
				//->where('death', 0)
				->whereYear('created_at', '=', $year)
				->whereMonth('created_at', '=', $month)
				->count();

			$accepted[$j] = Underwriting::where('death', 1)
			    // ->orWhere('disability',1)
			    // // ->orWhere('ci',1)
			    // // ->orWhere('medical',1)
				 ->whereYear('created_at', '=', $year)
				 ->whereMonth('created_at', '=', $month)
				->count();
			}else{
				$rejected[$j] ='';
				$accepted[$j]='';
			}
		}
 
		

		
		

		//$pathFile    = resource_path('reporting/UW_Ratio_V1.0.xlsx');
		$pathFile    = resource_path('reporting/Percentage_of_Decline.xlsx');
		$columnStart = 'c';
		$columnEnd   = 'n';

		$spreadsheet = IOFactory::load($pathFile);
		$sheet       = $spreadsheet->getActiveSheet();

		$index = 1;
		for ($i = $columnStart; $i <= $columnEnd; $i++) {
			$sheet->setCellValue($i . 10, $rejected[$index]);
			$sheet->setCellValue($i . 11, $accepted[$index]);
			$index++;
		}

		//$sheet->setCellValue('c5', $year);

		$filename = "{$year}-Underwriting-Rejection-" . str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".xlsx";

		try {
			$writer = new Xlsx($spreadsheet);
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");
			$writer->save("php://output");
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}

	public function farreport(Request $request)
	{
		$this->validate($request, [
			'start' => 'required',
		 ]);


		$selected      = $request->get('start');
		//strtotime($year);
		$month =Carbon::parse( $selected  )->format('m');
		$year =Carbon::parse( $selected  )->format('Y');
		//dd($year);
		$malaysianfp = [];
		$malaysianfn = [];
		$malaysiantp = [];
		$malaysiantn = [];
		$passportfp = [];
		$passportfn = [];
		$passporttp = [];
		$passporttn = [];


		$kycdetailsfn =CustomerVerificationDetail::whereIn('classification',['False Negative'])->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->get();
		$kycdetailsfp =CustomerVerificationDetail::whereIn('classification',['False Positive'])->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->get();
		$kycdetailstn =CustomerVerificationDetail::whereIn('classification',['True Negative'])->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->get();
		$kycdetailstp =CustomerVerificationDetail::whereIn('classification',['True Positive'])->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->get();
		//dd($kycdetailsfp);
		foreach($kycdetailsfn as $kycdetailfn){
			$individualid =customerVerification::Where('id',$kycdetailfn->kyc_id)->first()->individual_id;
			$individualnationality =Individual::Where('id',$individualid)->first()->nationality;
			//dd($individualnationality);
			if($individualnationality =='Malaysian'){
				$malaysianfn[]=$kycdetailfn;
			}
			else{
                $passportfn[]=$kycdetailfn;
			}
			
		}

		foreach($kycdetailsfp as $kycdetailfp){
			$individualid =customerVerification::Where('id',$kycdetailfp->kyc_id)->first()->individual_id;
			$individualnationality =Individual::Where('id',$individualid)->first()->nationality;
			//dd($individualnationality);
			if($individualnationality =='Malaysian'){
				$malaysianfp[]=$kycdetailfp;
			}
			else{
                $passportfp[]=$kycdetailfp;
			}
			
		}

		foreach($kycdetailstn as $kycdetailtn){
			$individualid =customerVerification::Where('id',$kycdetailtn->kyc_id)->first()->individual_id;
			$individualnationality =Individual::Where('id',$individualid)->first()->nationality;
			//dd($individualnationality);
			if($individualnationality =='Malaysian'){
				$malaysiantn[]=$kycdetailtn;
			}
			else{
                $passporttn[]=$kycdetailtn;
			}
			
		}
		foreach($kycdetailstp as $kycdetailtp){
			$individualid =customerVerification::Where('id',$kycdetailtp->kyc_id)->first()->individual_id;
			$individualnationality =Individual::Where('id',$individualid)->first()->nationality;
			//dd($individualnationality);
			if($individualnationality =='Malaysian'){
				$malaysiantp[]=$kycdetailtp;
			}
			else{
                $passporttp[]=$kycdetailtp;
			}
			
		}
		//dd(count($passportfp),count($malaysianfp));

		 for ($j = 1; $j <= 12; $j++) {
			if($j == $month){
			$falsepositivema[$j] =count($malaysianfp);

			$falsenegativema[$j] = count($malaysianfn);
			$falsepositivema[$j] = count($malaysianfp);
			$truenegativema[$j]  = count($malaysiantn);
			$truepositivema[$j]  = count($malaysiantp);
			$falsenegativefr[$j] = count($passportfn);
			$falsepositivefr[$j] = count($passportfp);
			$truenegativefr[$j]  = count($passporttn);
			$truepositivefr[$j]  = count($passporttp);

			}else{
			
			$falsenegativema[$j] = '';
			$falsepositivema[$j] = '';
			$truenegativema[$j]  = '';
			$truepositivema[$j]  = '';
			$falsenegativefr[$j] = '';
			$falsepositivefr[$j] = '';
			$truenegativefr[$j]  = '';
			$truepositivefr[$j]  = '';

			}

		 }

		

		
		$pathFile    = resource_path('reporting/far.xlsx');
		$columnStart = 'c';
		$columnEnd   = 'n';
        
		$spreadsheet = IOFactory::load($pathFile);
		$sheet       = $spreadsheet->getActiveSheet();

		$index = 1;
		for ($i = $columnStart; $i <= $columnEnd; $i++) {
			$sheet->setCellValue($i . 11, $truepositivema[$index]);
			$sheet->setCellValue($i . 12, $truenegativema[$index]);
			$sheet->setCellValue($i . 13, $falsepositivema[$index]);
			$sheet->setCellValue($i . 14, $falsenegativema[$index]);
			$sheet->setCellValue($i . 17, $truepositivefr[$index]);
			$sheet->setCellValue($i . 18, $truenegativefr[$index]);
			$sheet->setCellValue($i . 19, $falsepositivefr[$index]);
			$sheet->setCellValue($i . 20, $falsenegativefr[$index]);
			$index++;
		}

		//$sheet->setCellValue('c5', $year);

		$filename = "{$year}-FAR-Classification-" . str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".xlsx";

		try {
			$writer = new Xlsx($spreadsheet);
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");
			$writer->save("php://output");
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}

	public function tatreport(Request $request)
	{
		$this->validate($request, [
			'start' => 'required',
		 ]);


		$selected      = $request->get('start');
		//strtotime($year);
		$month =Carbon::parse( $selected  )->format('m');
		$year =Carbon::parse( $selected  )->format('Y');
		//dd($year);
		$medical_issuance_one=Coverage::where('product_name','Medical')->where('time_diff_issuance','<=',10)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$medical_issuance_two=Coverage::where('product_name','Medical')->where('time_diff_issuance','>',10)->where('time_diff_issuance','<=',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$medical_issuance_three=Coverage::where('product_name','Medical')->where('time_diff_issuance','>',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$ci_issuance_one=Coverage::where('product_name','Critical Illness')->where('time_diff_issuance','<=',10)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$ci_issuance_two=Coverage::where('product_name','Critical Illness')->where('time_diff_issuance','>',10)->where('time_diff_issuance','<=',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$ci_issuance_three=Coverage::where('product_name','Critical Illness')->where('time_diff_issuance','>',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$tpd_issuance_one=Coverage::where('product_name','Disability')->where('time_diff_issuance','<=',10)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$tpd_issuance_two=Coverage::where('product_name','Disability')->where('time_diff_issuance','>',10)->where('time_diff_issuance','<=',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$tpd_issuance_three=Coverage::where('product_name','Disability')->where('time_diff_issuance','>',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$acc_issuance_one=Coverage::where('product_name','Accident')->where('time_diff_issuance','<=',10)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$acc_issuance_two=Coverage::where('product_name','Accident')->where('time_diff_issuance','>',10)->where('time_diff_issuance','<=',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$acc_issuance_three=Coverage::where('product_name','Accident')->where('time_diff_issuance','>',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
        $death_issuance_one=Coverage::where('product_name','Death')->where('time_diff_issuance','<=',10)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$death_issuance_two=Coverage::where('product_name','Death')->where('time_diff_issuance','>',10)->where('time_diff_issuance','<=',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();
		$death_issuance_three=Coverage::where('product_name','Death')->where('time_diff_issuance','>',20)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->count();

		
		//dd(count($passportfp),count($malaysianfp));

		 for ($j = 1; $j <= 12; $j++) {
			if($j == $month){
			$medone[$j] = $medical_issuance_one;

			$medtwo[$j] = $medical_issuance_two;
			$medthree[$j] = $medical_issuance_three;
			$cione[$j]  = $ci_issuance_one;
			$citwo[$j]  = $ci_issuance_two;
			$cithree[$j] = $ci_issuance_three;
			$tpdone[$j] =  $tpd_issuance_one;
			$tpdtwo[$j]  = $tpd_issuance_two;
			$tpdthree[$j]  =$tpd_issuance_three;
			$accone[$j] =$acc_issuance_one;
			$acctwo[$j] =$acc_issuance_two;
			$accthree[$j] =$acc_issuance_three;
			$deathone[$j] =$death_issuance_one;
			$deathtwo[$j] =$death_issuance_two;
			$deaththree[$j] =$death_issuance_three;
			$medghone[$j]=0;
			$medghtwo[$j]=0;
			$medghthree[$j]=0;


			}else{
			
				$medone[$j] = '';

				$medtwo[$j] = '';
				$medthree[$j] = '';
				$cione[$j]  = '';
				$citwo[$j]  = '';
				$cithree[$j] = '';
				$tpdone[$j] =  '';
				$tpdtwo[$j]  = '';
				$tpdthree[$j]  ='';
				$accone[$j] ='';
				$acctwo[$j] ='';
				$accthree[$j] ='';
				$deathone[$j] ='';
			    $deathtwo[$j] ='';
			    $deaththree[$j] ='';
				$medghone[$j]='';
			    $medghtwo[$j]='';
			    $medghthree[$j]='';

			}

		 }
		$pathFile    = resource_path('reporting/tat.xlsx');
		$columnStart = 'd';
		$columnEnd   = 'o';
        
		$spreadsheet = IOFactory::load($pathFile);
		$sheet       = $spreadsheet->getActiveSheet();

		$index = 1;
		for ($i = $columnStart; $i <= $columnEnd; $i++) {
			$sheet->setCellValue($i . 10, $deathone[$index]);
			$sheet->setCellValue($i . 11, $deathtwo[$index]);
			$sheet->setCellValue($i . 12, $deaththree[$index]);
			$sheet->setCellValue($i . 13, $tpdone[$index]);
			$sheet->setCellValue($i . 14, $tpdtwo[$index]);
			$sheet->setCellValue($i . 15, $tpdthree[$index]);
			$sheet->setCellValue($i . 16, $accone[$index]);
			$sheet->setCellValue($i . 17, $acctwo[$index]);
			$sheet->setCellValue($i . 18, $accthree[$index]);
			$sheet->setCellValue($i . 19, $cione[$index]);

			$sheet->setCellValue($i . 20, $citwo[$index]);
			$sheet->setCellValue($i . 21, $cithree[$index]);
			$sheet->setCellValue($i . 22, $medone[$index]);

			$sheet->setCellValue($i . 23, $medtwo[$index]);
			$sheet->setCellValue($i . 24, $medthree[$index]);
			$sheet->setCellValue($i . 25, $medghone[$index]);

			$sheet->setCellValue($i . 26, $medghtwo[$index]);
			$sheet->setCellValue($i . 27, $medghthree[$index]);


			$index++;
		}

		//$sheet->setCellValue('c5', $year);

		$filename = "{$year}-TAT-" . str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".xlsx";

		try {
			$writer = new Xlsx($spreadsheet);
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");
			$writer->save("php://output");
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}

	public function underwritingRejectionAnalysis(Request $request)
	{
		$this->validate($request, [
			'from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
			'to'       => 'required|date|date_format:Y/m/d',
			'accepted' => 'nullable|in:on'
		]);

		$from     = Carbon::parse($request->input('from'))->startOfDay();
		$to       = Carbon::parse($request->input('to'))->endOfDay();
		$accepted = $request->input('accepted');

		$mainQuery = Underwriting::whereBetween('created_at', [$from, $to])
			->with('individual');

		if ($accepted == 'on') {
			$data = $mainQuery->get();
		} else {
			$data = $mainQuery
				->where('death', 0)
				->where('disability', 0)
				->where('ci', 0)
				->where('medical', 0)
				->get();
		}

		if ($data->count() == 0) {
			return redirect()->back()->with('error', __('web/messages.no_report'));
		}

		$breadcrumbs = [
			['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
			['name' => 'Reporting', 'link' => route('admin.reports.index')],
		];

		return view('admin.reports.underwriting-rejection-analysis', compact('data', 'breadcrumbs'));
	}

	public function underwritingRejectionAnalysisByUser($uuid)
	{
		$underwriting = Underwriting::where('uuid', $uuid)->first();

		$breadcrumbs = [
			['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
			['name' => 'Reporting', 'link' => route('admin.reports.index')],
			['name' => 'Underwriting Rejection Analysis For ' . $underwriting->individual->name, 'link' => route('admin.reports.underwriting.rejection.analysis.by.user', $underwriting->uuid)],
		];

		return view('admin.reports.underwriting-rejection-analysis-by-user', compact('underwriting', 'breadcrumbs'));
	}

	public function exportCsv()
	{
		$breadcrumbs = [
			['name' => 'Admin Area', 'link' => route('admin.dashboard.main')],
			['name' => 'Export Member Data', 'link' => url()->current()],
		];

		return view('admin.reports.export_member_data', compact('breadcrumbs'));
	}

	public function successfulTransaction(Request $request)
	{
		try {
			$this->validate($request, [
				'date_from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
				'date_to'       => 'required|date|date_format:Y/m/d'
			]);

			$from     = Carbon::parse($request->input('date_from'))->startOfDay();
			$to       = Carbon::parse($request->input('date_to'))->endOfDay();

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet       = $spreadsheet->getActiveSheet();

			$arrHeadings	=	[
				__('web/messages.transactions_ref'),
				__('web/messages.transactions_id'),
				__('web/messages.amount'),
				__('web/messages.card_type'),
				__('web/messages.card_no'),
				__('web/messages.order_ref'),
				__('web/messages.status'),
				'Bank Approval Code (From Payment Gateway)',
				__('web/messages.payment_at'),
				'Coverage Ref No / Policy No',
				'Coverage Amount',
				'Owner (Insured) - CU',
				'Payer',
				'Covered',
				'Product',
				'Premium Amount',
				'Indicator Policyowner (B40)',
				__('web/messages.status'),
				'Payment Term',
				'Monthly Premium Due'
			];
			$sheet->fromArray([$arrHeadings], NULL, 'A1');

			$rowCount = 2;
			$transactions		=	Transaction::whereBetween('created_at', [$from, $to])->orderBy('ref_no', 'desc')->get();
			foreach ($transactions as $transaction)
			{
				$transaction_ref	=	$transaction->transaction_ref;
				$transaction_id		=	$transaction->transaction_id;
				$amount				=	'RM'.$transaction->amount;
				$card_type			=	$transaction->card_type;
				$card_no			=	$transaction->card_no;
				$date				=	Carbon::parse($transaction->date)->format('d/m/Y H:i');
				$success			=	$transaction->success ? Enum::TRANSACTION_STATUS_SUCCESSFUL : Enum::TRANSACTION_STATUS_UNSUCCESSFUL;

				$order_ref_no	=	$transaction->order->ref_no;
				$orderCoverages	=	$transaction->order->coverages()->get();
				foreach ($orderCoverages as $coverage) 
				{
					$coverage_ref_no	=	$coverage->ref_no;
					$coverage_amount    =   'RM'. number_format($coverage->coverage,2);
					$owner_name			=	$coverage->owner->name ?? '';
					$payer_name			=	$coverage->sponsored ==1? 'DearTime Charity Fund': $coverage->payer->profile->name  ?? '';
					$coveraged_name		=	$coverage->covered->name ?? '';
					$product_name		=	$coverage->product_name;
					$status				=	$coverage->status;
					$payment_term		=	$coverage->payment_term;
					$premium_amount		=	$coverage->payment_term == 'monthly' ? 'RM' . number_format($coverage->payment_monthly, 2) : 'RM' . number_format($coverage->payment_annually, 2);
					$next_payment_on	=	Carbon::parse($coverage->next_payment_on)->format('d/m/Y H:i');

					$result				=	[];
					$result[]			=	$transaction_ref;
					$result[]			=	$transaction_id;
					$result[]			=	$amount;
					$result[]			=	$card_type;
					$result[]			=	$card_no;
					$result[]			=	$order_ref_no;
					$result[]			=	$success;
					$result[]			=	'';
					$result[]			=	$date;
					$result[]			=	$coverage_ref_no;
					$result[]			=	$coverage_amount;
					$result[]			=	$owner_name;
					$result[]			=	$payer_name;
					$result[]			=	$coveraged_name;
					$result[]			=	$product_name;
					$result[]			=	$premium_amount;
					$result[]			=	'';
					$result[]			=	$status;
					$result[]			=	$payment_term;
					$result[]			=	$next_payment_on;				

					$sheet->fromArray([$result], NULL, 'A'.$rowCount);
					$rowCount++;
				}
			}

			$filename = 'Successful-Transactions-'.date('d-m-y-' . substr((string)microtime(), 1, 8)) . ".xlsx";

			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			// redirect output to client browser
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");	
			$writer->save('php://output');
			exit;
		} catch (\Exception $e) {
			echo 'Expection :';
			dd($e->getMessage());
		}
	}


	public function Customerlist(Request $request)
	{
		try {
			$this->validate($request, [
				'date_from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
				'date_to'       => 'required|date|date_format:Y/m/d'
			]);

			$from     = Carbon::parse($request->input('date_from'))->startOfDay();
			$to       = Carbon::parse($request->input('date_to'))->endOfDay();

			$userExports = CustomerReport::whereBetween('register', [$from, $to])
        ->orderByDesc('user_ref')
        ->get();

    // Create a new spreadsheet and set the headers
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'User Reference');
    $sheet->setCellValue('B1', 'Type');
    $sheet->setCellValue('C1', 'Name');
    $sheet->setCellValue('D1', 'Email');
    $sheet->setCellValue('E1', 'Mobile');
    $sheet->setCellValue('F1', 'NRIC');
    $sheet->setCellValue('G1', 'Registration Date');

    // Populate the spreadsheet with data
    $row = 2;
    foreach ($userExports as $userExport) {
        $sheet->setCellValue('A' . $row, $userExport->user_ref);
        $sheet->setCellValue('B' . $row, $userExport->type);
        $sheet->setCellValue('C' . $row, $userExport->name);
        $sheet->setCellValue('D' . $row, $userExport->email);
        $sheet->setCellValue('E' . $row, $userExport->mobile);
        $sheet->setCellValue('F' . $row, $userExport->nric);
        $sheet->setCellValue('G' . $row, $userExport->register);

        $row++;
    }

    // Create the Excel file
	//$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer = new Xlsx($spreadsheet);


	ob_start();
    $writer->save('php://output');
    $excelFileContent = ob_get_clean();

    // Generate a download response
    $fileName = 'CustomerList_' . now()->format('YmdHis') . '.xlsx';
    return Response::make($excelFileContent, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ]);

	
		} catch (\Exception $e) {
			echo 'Expection :';
			dd($e->getMessage());
		}
	}

	public function Referrallist(Request $request)
	{
		try {
			$this->validate($request, [
				'date_from' => 'required|date|date_format:Y/m/d|before_or_equal:to',
				'date_to' => 'required|date|date_format:Y/m/d'
			]);
	
			$from = Carbon::parse($request->input('date_from'))->startOfDay();
			$to = Carbon::parse($request->input('date_to'))->endOfDay();
	
			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
	
			$arrHeadings = [
				'Referrer Name',
				'Referee Name',
				'Amount',
				'Bank Name',
				'Bank Account number',
				'Payment Status',
				'Month',
				'Year',
			];
			$sheet->fromArray([$arrHeadings], NULL, 'A1');
	
			$rowCount = 2;
			$referrals = Referral::whereBetween('created_at', [$from, $to])->orderBy('id', 'desc')->get();
	
			foreach ($referrals as $referral) {
				$referrer_name = $referral->from_referral_name;
				$referee_name = $referral->to_referee_name;
				if($referral->amount == 0){
					$referral->amount = '0';
				}else{
					$referral->amount;
				}
				$amount = $referral->amount;
				$payment_status = $referral->payment_status;
				$month = $referral->month;
				$year  = $referral->year;
	
				$individuals = Individual::where('user_id', $referral->from_referrer)->first();

				$userr = User::where('id',$referral->from_referrer)->first();
	            
				if($userr->corporate_type != 'payorcorporate'){

				$bank_name = $individuals->bankAccounts()->latest()->first()->bank_name;

				$bank_account = $individuals->bankAccounts()->latest()->first()->account_no;
				
		
	
						$result = [];
						$result[] = $referrer_name;
						$result[] = $referee_name;
						$result[] = $amount;
						$result[] = $bank_name;
						$result[] = $bank_account;
						$result[] = $payment_status;
						$result[] = $month;
						$result[] = $year;
						
						$sheet->fromArray([$result], NULL, 'A' . $rowCount);
						$rowCount++;
					}
				}
			$filename = 'Referral-List-' . date('d-m-y-' . substr((string)microtime(), 1, 8)) . ".xlsx";
	
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header('X-Vapor-Base64-Encode:true');
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment; filename={$filename}");
			$writer->save('php://output');
			exit;
		} catch (\Exception $e) {
			echo 'Exception:';
			dd($e->getMessage());
		}
	}
	
	/*public function exportCsv()
	{
		$actions = Action::query()
			->where('status', Enum::ACTION_STATUS_EXECUTED)
			//->whereBetween('created_at',[Carbon::now()->startOfDay(),Carbon::now()->endOfDay()])
			->orderBy('created_at', 'asc')
			->get();

		if (empty($actions)) {
			return;
		}

		$data        = [];
		$actionUsers = [];

		foreach ($actions as $action) {
			if (isset($actionUsers[$action->user_id])) {
				array_push($actionUsers[$action->user_id], $action->id);
			} else {
				$actionUsers[$action->user_id] = [];
				array_push($actionUsers[$action->user_id], $action->id);
			}
		}

		foreach ($actionUsers as $actionUser) {
			$checkMemberAddition = FALSE;
			$checkTerminate      = FALSE;
			$checkAmendment      = FALSE;
			$checkPlanChange     = FALSE;
			$oldDeductible       = 0;

			foreach ($actionUser as $actionId) {
				$action = Action::find($actionId);

				$coverage = $action->coverages()
					->where('product_name', 'Medical')
					->whereIn('status', [Enum::COVERAGE_STATE_ACTIVE, Enum::COVERAGE_STATUS_DECREASE_UNPAID])
					->orderBy('created_at', 'desc')
					->first();

				if (empty($coverage)) {
					$coverage = $action->user->profile->coverages_owner()
						->where('product_name', 'Medical')
						->where('state', Enum::COVERAGE_STATE_ACTIVE)
						->orderBy('created_at', 'desc')
						->first();
				}

				if (empty($coverage)) {
					continue;
				}

				if (!$checkMemberAddition && ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO)) {
					continue;
				} else {
					$checkMemberAddition = TRUE;
				}

				if ($action->type == Enum::ACTION_TYPE_MEMEBR_ADDITION) {
					$checkTerminate = FALSE;
				}

				if (!$checkTerminate) {
					if (!empty($coverage)) {

						if (!$checkAmendment && ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO)) {
							$checkAmendment = TRUE;
						} elseif ($checkAmendment && ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO)) {
							continue;
						}

						if (!$checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE)) {
							$checkPlanChange = TRUE;
							$oldDeductible   = $coverage->deductible;
						} elseif ($checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE) && ($oldDeductible != $coverage->deductible)) {
							$oldDeductible = $coverage->deductible;
						} elseif ($checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE) && ($oldDeductible == $coverage->deductible)) {
							continue;
						}

						if ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO) {
							$actionType = Enum::ACTION_TYPE_AMENDMENT;
						} else {
							$actionType = $action->type;
						}

						array_push($data, [
							'no'               => $action->user->ref_no,
							'action'           => $actionType,
							'category'         => 'DT' . $coverage->deductible,
							'effective_date'   => Carbon::parse($coverage->last_payment_on)->format('d/m/y'),
							'name'             => $action->user->profile->name,
							'dependent_name'   => NULL,
							'relationship'     => NULL,
							'principal_ic'     => $action->user->profile->nric,
							'dependent_ic'     => NULL,
							'dob'              => Carbon::parse($action->user->profile->dob)->format('d/m/y'),
							'gender'           => $action->user->profile->gender,
							'company_name'     => NULL,
							'subsidiary_name'  => NULL,
							'department_name'  => NULL,
							'employee_no'      => NULL,
							'plan'             => 'Decutible ' . $coverage->deductible,
							'plan_description' => 'Decutible ' . $coverage->deductible,
							'bank_name'        => $action->user->profile->bankAccounts()->latest()->first()->bank_name,
							'account_no'       => $action->user->profile->bankAccounts()->latest()->first()->account_no,
							'email'            => $action->user->email,
							'remarks'          => NULL,
							'cost_centre'      => NULL,
							'branch_name'      => NULL,
							'position'         => NULL,
						]);
					}
				}

				if ($action->type == Enum::ACTION_TYPE_TERMINATE) {
					$checkTerminate      = TRUE;
					$checkMemberAddition = FALSE;
					$checkAmendment      = FALSE;
					$checkPlanChange     = FALSE;
				}

				if ($checkTerminate) {
					continue;
				}
			}
		}

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();

		$rowCount = 1;
		foreach ($data as $items) {
			$sheet->setCellValue('A' . $rowCount, $items['no']);
			$sheet->setCellValue('B' . $rowCount, $items['action']);
			$sheet->setCellValue('C' . $rowCount, $items['category']);
			$sheet->setCellValue('D' . $rowCount, $items['effective_date']);
			$sheet->setCellValue('E' . $rowCount, $items['name']);
			$sheet->setCellValue('F' . $rowCount, $items['dependent_name']);
			$sheet->setCellValue('G' . $rowCount, $items['relationship']);
			$sheet->setCellValue('H' . $rowCount, $items['principal_ic']);
			$sheet->setCellValue('I' . $rowCount, $items['dependent_ic']);
			$sheet->setCellValue('J' . $rowCount, $items['dob']);
			$sheet->setCellValue('K' . $rowCount, $items['gender']);
			$sheet->setCellValue('L' . $rowCount, $items['company_name']);
			$sheet->setCellValue('M' . $rowCount, $items['subsidiary_name']);
			$sheet->setCellValue('N' . $rowCount, $items['department_name']);
			$sheet->setCellValue('O' . $rowCount, $items['employee_no']);
			$sheet->setCellValue('P' . $rowCount, $items['plan']);
			$sheet->setCellValue('Q' . $rowCount, $items['plan_description']);
			$sheet->setCellValue('R' . $rowCount, $items['bank_name']);
			$sheet->setCellValue('S' . $rowCount, $items['account_no']);
			$sheet->setCellValue('T' . $rowCount, $items['email']);
			$sheet->setCellValue('U' . $rowCount, $items['remarks']);
			$sheet->setCellValue('W' . $rowCount, $items['cost_centre']);
			$sheet->setCellValue('X' . $rowCount, $items['branch_name']);
			$sheet->setCellValue('Y' . $rowCount, $items['position']);
			$rowCount++;
		}

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->setDelimiter('|');
		$writer->setEnclosure('');
		$writer->setLineEnding("\r\n");

		$filename = str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".txt";

		header('X-Vapor-Base64-Encode:true');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type: application/csv');
		header("Content-Disposition: attachment; filename={$filename}");
		$writer->save("php://output");
	} */

	public function exportMemberData(Request $request)
	{
		$this->validate($request, [
			'from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
			'to'       => 'required|date|date_format:Y/m/d'
		]);

		$from     = Carbon::parse($request->input('from'))->startOfDay();
		$to       = Carbon::parse($request->input('to'))->endOfDay();

		$actions = Action::query()
			->where('status', Enum::ACTION_STATUS_EXECUTED)
			->whereBetween(DB::raw("DATE(updated_at)"), [$from, $to])
			->orderBy('updated_at', 'asc')
			->get();

		if (empty($actions)) {
			return;
		}
		// dump($actions);

		$data        = [];
		$actionUsers = [];
		//Missing / Not showing all product related users ex. JASMINE record not displayed
		//missing record fix
		$cov_product = [
			'Critical Illness' => 'CIL',
			'Death' => 'DTH',
			'Disability' => 'TPD',
			'Accident' => 'ADD',
			'Medical' => 'MED'
		];



		foreach ($actions as $action) {
			if (isset($actionUsers[$action->user_id])) {
				array_push($actionUsers[$action->user_id], $action->id);
			} else {
				$actionUsers[$action->user_id] = [];
				array_push($actionUsers[$action->user_id], $action->id);
			}
		}

		// dump($actionUsers);




		foreach ($actionUsers as $actionUser) {
			$checkMemberAddition = FALSE;
			$checkTerminate      = FALSE;
			$checkAmendment      = FALSE;
			$checkPlanChange     = FALSE;
			$oldDeductible       = 0;
			$gg = 0;



			foreach ($actionUser as $actionId) {
				$action = Action::find($actionId);

				$coverages = $action->coverages()
					// ->where('product_name', 'Medical')
					//->whereIn('status', [Enum::COVERAGE_STATE_ACTIVE, Enum::COVERAGE_STATUS_DECREASE_UNPAID, Enum::COVERAGE_STATUS_ACTIVE_INCREASED, Enum::COVERAGE_STATUS_ACTIVE_DECREASED])
					//->where('state', Enum::COVERAGE_STATE_ACTIVE)
					->orderBy('updated_at', 'desc')
					->get();

				

				if (empty($coverages) && ($action->user->profile ?? NULL)) {
					$coverages = $action->user->profile->coverages_owner()
						// ->where('product_name', 'Medical')
						->where('state', Enum::COVERAGE_STATE_ACTIVE)
						->orderBy('updated_at', 'desc')
						->first();
				}

				if (count($coverages)) {
					foreach ($coverages as $coverage) {

						if (!$checkAmendment && ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO)) {
							$checkAmendment = TRUE;
						} elseif ($checkAmendment && ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO)) {
							continue;
						}

						if (!$checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE)) {
							$checkPlanChange = TRUE;
							$oldDeductible   = $coverage->deductible;
						} elseif ($checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE) && ($oldDeductible != $coverage->deductible)) {
							$oldDeductible = $coverage->deductible;
						} elseif ($checkPlanChange && ($action->type == Enum::ACTION_TYPE_PLAN_CHANGE) && ($oldDeductible == $coverage->deductible)) {
							// continue;
						}

						if ($action->type == Enum::ACTION_TYPE_PARTICULAR_CHANGE || $action->type == Enum::ACTION_TYPE_BANK_INFO) {
							$actionType = Enum::ACTION_TYPE_AMENDMENT;
						} else {
							$actionType = $action->type;
						}

						// dump($action->user->profile);
						// dump($action->user->profile->occupation['job']);

						// dump($action->user->profile->occupation['industry']->first()->name);
						// dump($coverage);

						$plan = NULL;
						$plan_description = NULL;
						if ($action->user->profile ?? NULL) {
							if ($coverage->product_name == 'Medical') {
								$plan = 'Deductible ' . $coverage->deductible;
								$plan_description = 'Deductible ' . $coverage->deductible;
								$last_payment_date = Carbon::parse($action->user->profile->dob)->addYears(70)->format('d/m/Y');
							} elseif ($coverage->product_name == 'Accident') {
								$last_payment_date = Carbon::parse($action->user->profile->dob)->addYears(70)->format('d/m/Y');
							} elseif ($coverage->product_name == 'Disability') {
								$last_payment_date = Carbon::parse($action->user->profile->dob)->addYears(70)->format('d/m/Y');
							} elseif ($coverage->product_name == 'Death') {
								$last_payment_date = Carbon::parse($action->user->profile->dob)->addYears(100)->format('d/m/Y');
							} elseif ($coverage->product_name == 'Critical Illness') {
								$last_payment_date = Carbon::parse($action->user->profile->dob)->addYears(100)->format('d/m/Y');
							}
						}
						$tpd_c = NULL;
						$cil_c = NULL;
						$dth_c = NULL;
						$add_c = NULL;
						// foreach ($action->user->profile->coverages_owner ?? [] as $cov) {
						// 	if ($cov->product_name == 'Accident') {
						// 		$add_c = $cov->RealCoverage;
						// 	} elseif ($cov->product_name == 'Disability') {
						// 		$tpd_c = $cov->RealCoverage;
						// 	} elseif ($cov->product_name == 'Death') {
						// 		$dth_c = $cov->RealCoverage;
						// 	} elseif ($cov->product_name == 'Critical Illness') {
						// 		$cil_c = $cov->RealCoverage;
						// 	}
						// }
						if ($coverage->product_name == 'Accident') {
							$add_c = $coverage->RealCoverage;
						} elseif ($coverage->product_name == 'Disability') {
							$tpd_c = $coverage->RealCoverage;
						} elseif ($coverage->product_name == 'Death') {
							$dth_c = $coverage->RealCoverage;
						} elseif ($coverage->product_name == 'Critical Illness') {
							$cil_c = $coverage->RealCoverage;
						}

						if ($actionType == 'Plan Change' && $coverage->product_name != 'Medical') {
							$under_coverage = 'RM' . $coverage->coverage;
						} else {
							$under_coverage = NULL;
						}

						if($coverage->status == Enum::COVERAGE_STATUS_ACTIVE_INCREASED || $coverage->status == Enum::COVERAGE_STATUS_ACTIVE_DECREASED || $coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID)
							$actionType		=	'Plan Change';

						$effectiveDate = Carbon::parse($coverage->last_payment_on)->format('d/m/Y');
						if($actionType == 'Terminate')
						{
							$coverageLastUpdateTime = Carbon::parse($coverage->last_payment_on);
							$actionExecTime = Carbon::parse($action->execute_on);
							$totalDaysDiff 	= $actionExecTime->diffInDays($coverageLastUpdateTime);

							if($totalDaysDiff > 15)
								$effectiveDate	=	Carbon::parse($coverage->next_payment_on)->format('d/m/Y');
						}

						if ($action->user->profile ?? NULL) {

							array_push($data, [
								'serial_number'    => $action->id,
								'action'           => $actionType,
								'no'               => $coverage->ref_no,
								//missing columns fixes 
								'effective_date'   => $effectiveDate,
								//'effective_date'   => Carbon::parse($coverage->created_at)->format('d/m/Y'),

								'last_payment_date'   => $last_payment_date,
								'next_payment_date'   => Carbon::parse($coverage->next_payment_on)->format('d/m/Y'),
								'name'             => $action->user->profile->name ?? NULL,
								'dependent_name'   => NULL,
								'relationship'     => NULL,
								'principal_ic'     => $action->user->profile->nric,
								'dependent_ic'     => NULL,
								'dob'              => Carbon::parse($action->user->profile->dob)->format('d/m/Y'),
								'gender'           => $action->user->profile->gender,
								// 'company_name'     => $action->user->profile->occupation['job']->industry->name,
								'company_name'     => NULL,
								'subsidiary_name'  => NULL,
								'department_name'  => NULL,
								'employee_no'      => NULL,
								'category'         => $cov_product[$coverage->product_name] ?? $coverage->product_name,
								'plan'             => $plan,
								'plan_description' => $plan_description,
								// 'coverage'         =>  $coverage->coverage,
								'tpd_coverage'         =>  $tpd_c,
								'cil_coverage'         =>  $cil_c,
								'dth_coverage'         =>  $dth_c,
								'add_coverage'         =>  $add_c,
								'premium' 		   => $coverage->payment_annually,
								'bank_name'        => $action->user->profile->bankAccounts()->latest()->first()->bank_name,
								'account_no'       => $action->user->profile->bankAccounts()->latest()->first()->account_no,
								'email'            => $action->user->email,
								'coverage'         =>  $under_coverage,
								// 'hand_phone'       => $action->user->profile->mobile ?? NULL,
								// 'remarks'          => NULL,
								// 'cost_centre'      => NULL,
								// 'branch_name'      => NULL,
								// 'position'         => NULL,
							]);
						}
						$gg++;
					}
				}
				else
				{
					if(!$action->user)
						continue;

					if(!$action->user->profile)
						continue;

					if(!$action->user->profile->coverages_owner)
						continue;

					$coverage = $action->user->profile->coverages_owner()
						->where('coverages.state', Enum::COVERAGE_STATE_ACTIVE)
						->orderBy('coverages.created_at', 'desc')
						->first();
					
					if(!$coverage)
						continue;

					$actionType = $action->type;

					$effectiveDate = Carbon::parse($action->created_at)->format('d/m/Y');
					if($actionType == 'Terminate')
					{
						$coverageLastUpdateTime = Carbon::parse($coverage->updated_at);
						$actionExecTime = Carbon::parse($action->execute_on);
						$totalDaysDiff 	= $actionExecTime->diffInDays($coverageLastUpdateTime);

						if($totalDaysDiff > 15)
							$effectiveDate	=	Carbon::parse($coverage->next_payment_on)->format('d/m/Y');
					}

					$dob	=	'';
					if($action->user->profile->dob?? NULL)
						$dob = Carbon::parse($action->user->profile->dob)->format('d/m/Y');
				if ($action->user->profile ?? NULL){
					array_push($data, [
						'serial_number'    => $action->id,
						'action'           => $actionType,
						'no'               => '',
						//missing columns fixes 
						//'effective_date'   => '',
						'effective_date'   => $effectiveDate,

						'last_payment_date'   => '',
						'next_payment_date'   => '',
						'name'             => $action->user->profile->name ?? NULL,
						'dependent_name'   => NULL,
						'relationship'     => NULL,
						'principal_ic'     => $action->user->profile->nric ?? '',
						'dependent_ic'     => NULL,
						'dob'              => $dob,
						'gender'           => $action->user->profile->gender ?? '',
						
						// 'company_name'     => $action->user->profile->occupation['job']->industry->name,
						'company_name'     => NULL,
						'subsidiary_name'  => NULL,
						'department_name'  => NULL,
						'employee_no'      => NULL,
						'category'         => '',
						'plan'             => '',
						'plan_description' => '',
						// 'coverage'         =>  $coverage->coverage,
						'tpd_coverage'         =>  '',
						'cil_coverage'         =>  '',
						'dth_coverage'         =>  '',
						'add_coverage'         =>  '',
						'premium' 		   => '',
						'bank_name'        => $action->user->profile->bankAccounts()->latest()->first()->bank_name ?? NULL,
						'account_no'       => $action->user->profile->bankAccounts()->latest()->first()->account_no ?? NULL,
						'email'            => $action->user->email ?? '',
						'coverage'         =>  '',
						// 'hand_phone'       => $action->user->profile->mobile ?? NULL,
						// 'remarks'          => NULL,
						// 'cost_centre'      => NULL,
						// 'branch_name'      => NULL,
						// 'position'         => NULL,
					]);
				}
				}


				if ($action->type == Enum::ACTION_TYPE_TERMINATE) {
					$checkTerminate      = TRUE;
					$checkMemberAddition = FALSE;
					$checkAmendment      = FALSE;
					$checkPlanChange     = FALSE;
				}

				if ($checkTerminate) {
					continue;
				}
			}
			// $gg++;
			// if ($gg > 0)
			// 	break;
		}

		// dd('Finished');
		//dd($data);

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();

		$rowCount = 1;
		foreach ($data as $items) {
			$sheet->setCellValue('A' . $rowCount, $rowCount);
			$sheet->setCellValue('B' . $rowCount, $items['action']);
			$sheet->setCellValue('C' . $rowCount, $items['no']);
			$sheet->setCellValue('D' . $rowCount, $items['effective_date']);
			$sheet->setCellValue('E' . $rowCount, $items['last_payment_date']);
			$sheet->setCellValue('F' . $rowCount, $items['next_payment_date']);
			$sheet->setCellValue('G' . $rowCount, $items['name']);
			$sheet->setCellValue('H' . $rowCount, $items['dependent_name']);
			$sheet->setCellValue('I' . $rowCount, $items['relationship']);
			$sheet->setCellValue('J' . $rowCount, $items['principal_ic']);
			$sheet->setCellValue('K' . $rowCount, $items['dependent_ic']);
			$sheet->setCellValue('L' . $rowCount, $items['dob']);
			$sheet->setCellValue('M' . $rowCount, $items['gender']);
			$sheet->setCellValue('N' . $rowCount, $items['company_name']);
			$sheet->setCellValue('O' . $rowCount, $items['subsidiary_name']);
			$sheet->setCellValue('P' . $rowCount, $items['department_name']);
			$sheet->setCellValue('Q' . $rowCount, $items['employee_no']);
			$sheet->setCellValue('R' . $rowCount, $items['category']);
			$sheet->setCellValue('S' . $rowCount, $items['plan']);
			$sheet->setCellValue('T' . $rowCount, $items['plan_description']);
			// $sheet->setCellValue('U' . $rowCount, $items['coverage']);
			//Fix for showing Premium
			$sheet->setCellValue('U' . $rowCount, $items['tpd_coverage']);
			$sheet->setCellValue('V' . $rowCount, $items['cil_coverage']);
			$sheet->setCellValue('W' . $rowCount, $items['dth_coverage']);
			$sheet->setCellValue('X' . $rowCount, $items['add_coverage']);
			// $sheet->setCellValue('Y' . $rowCount, $items['premium']);
			//			$sheet->setCellValue('S' . $rowCount,$items['plan_description']);
			$sheet->setCellValue('Y' . $rowCount, $items['bank_name']);
			$sheet->setCellValue('Z' . $rowCount, $items['account_no']);
			$sheet->setCellValue('AA' . $rowCount, $items['email']);
			$sheet->setCellValue('AB' . $rowCount, '');
			// $sheet->setCellValue('X' . $rowCount, $items['hand_phone']);
			// $sheet->setCellValue('Y' . $rowCount, $items['remarks']);
			// $sheet->setCellValue('Z' . $rowCount, $items['cost_centre']);
			// $sheet->setCellValue('AA' . $rowCount, $items['branch_name']);
			// $sheet->setCellValue('AB' . $rowCount, $items['position']);
			$rowCount++;
		}

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		$writer->setDelimiter('|');
		$writer->setEnclosure('');
		$writer->setLineEnding("\r\n");

		$filename = str_replace(".", "", date('d-m-y-' . substr((string)microtime(), 1, 8))) . ".txt";

		header('X-Vapor-Base64-Encode:true');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type: application/csv');
		header("Content-Disposition: attachment; filename={$filename}");
		$writer->save("php://output");
	}
	
		public function exportMemebersUwData_bk(Request $request)
{


	try {

	$this->validate($request, [
		'date_from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
		'date_to'       => 'required|date|date_format:Y/m/d'
	]);

	$from     = Carbon::parse($request->input('date_from'))->startOfDay();
	$to       = Carbon::parse($request->input('date_to'))->endOfDay();




	$groupedAnswers = $this->readJson();


	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$borderColor = new Color(Color::COLOR_BLACK);

	$row = 1; // Start from the fifth row
	$col = 6; // Start from the eighth column (H)

	$headers = ["Ref No", "Name", "NRIC", "Created at", "BMI"];
	$columnIndex = 1; // Starting from column B

	foreach ($headers as $header) {
		$sheet->setCellValueByColumnAndRow($columnIndex, $row, $header);
	

		$columnIndex++;
	}



	$answer_title_location=[];

	foreach ($groupedAnswers as $question => $answers) {
		$startCol = $col; // Remember the starting column for this question

		// Set the question title in the current cell
		$sheet->setCellValueByColumnAndRow($col, $row, $this->cleanUtf8($question));

		$row++;

		foreach ($answers as $answer) {
			$sheet->setCellValueByColumnAndRow($col, $row, $this->cleanUtf8($answer['answer_title']));

		
			$answer_title_location[$answer['id']] = $col;
			$col++; 
		}

		$row = 1;
	}


	$underwritings = ViewIndividualsUw::whereBetween('created_at', [$from, $to])->get();

	
	$row=$row+2;  

	foreach ($underwritings as $underwriting) {
		// Write the ref_no and created_at values
		$sheet->setCellValueByColumnAndRow(2, $row, $this->cleanUtf8($underwriting->name));
		$sheet->setCellValueByColumnAndRow(3, $row, $this->cleanUtf8($underwriting->nric));
		$sheet->setCellValueByColumnAndRow(1, $row, $this->cleanUtf8($underwriting->ref_no));
		$sheet->setCellValueByColumnAndRow(4, $row, $this->cleanUtf8($underwriting->created_at));
		$sheet->setCellValueByColumnAndRow(5, $row, $this->cleanUtf8(round(($underwriting->answers['weight'] / $underwriting->answers['height'] / $underwriting->answers['height']) * 10000)));

		foreach ($groupedAnswers as $question => $answers) {
			foreach ($answers as $answer) {
				if (in_array($answer['id'], $underwriting->answers['answers'])) {
					$col = $answer_title_location[$answer['id']]-1;
					$sheet->setCellValueByColumnAndRow($col, $row, $this->cleanUtf8($answer['answer_title']));
				}
			}
		}

		$row++;  // Move to the next row for the next underwriting record
	}
	
	$filenamerandam= date('d-m-y-' . substr((string)microtime(), 1, 8));

	// Save the Excel file to php://output and prompt a download
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="UnderWriting_'.$filenamerandam.'.xlsx"');
	header('Cache-Control: max-age=0');

	$writer = new Xlsx($spreadsheet);
	$writer->save('php://output');
	exit;
		}
		
		catch (\Exception $e) {
			\Log::error('Exception caught', [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
			return response()->json(['error' => 'An error occurred while processing your request'], 500);
		}


	}


	public function exportMemebersUwData(Request $request)
{
    try {
        $this->validate($request, [
            'date_from' => 'required|date|date_format:Y/m/d|before_or_equal:date_to', // Corrected validation rule
            'date_to'   => 'required|date|date_format:Y/m/d'
        ]);

        $from = Carbon::parse($request->input('date_from'))->startOfDay();
        $to   = Carbon::parse($request->input('date_to'))->endOfDay();

        $groupedAnswers = $this->readJson(); // Ensure this method returns the expected data structure
        $underwritings = ViewIndividualsUw::whereBetween('created_at', [$from, $to])->get();

        $filename = 'UnderWriting_' . date('d-m-y-H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

		$headers = ["Ref No", "Name", "NRIC", "Created at", "BMI"]; // Initial Headers
		$subheader = ["","","","",""]; // Initialize subheader with empty values for initial columns
		
		// Prepare answer titles and column mapping
		$answer_title_location = [];
		foreach ($groupedAnswers as $question => $answers) {
			$headers[] = $question; // Add question to headers
			$questionColumnIndex = count($headers) - 1; // Store the column index of the question
		
			foreach ($answers as $answer) {
				if (!isset($subheader[$questionColumnIndex])) {
					$subheader[$questionColumnIndex] = $answer['answer_title']; // Set subheader for the question column
				} else {
					$subheader[] = $answer['answer_title']; // Add subsequent answer titles in new columns
					$headers[] = ""; // Add empty header for new columns
				}
				$answer_title_location[$answer['id']] = count($subheader) ; // Map answer ID to column number
			}
		}
		
		// Ensure both headers and subheaders have the same number of columns
		while(count($subheader) < count($headers)) {
			$subheader[] = ""; // Fill subheader with empty values to match headers length
		}
		
		fputcsv($output, $headers);
		fputcsv($output, $subheader);
		

        foreach ($underwritings as $underwriting) {
            // Initialize row data
            $rowData = array_fill(0, count($headers), '');

            // Populate standard underwriting data
            $rowData[0] = $underwriting->ref_no ?? '';
            $rowData[1] = $underwriting->name ?? '';
            $rowData[2] = $underwriting->nric ?? '';
            $rowData[3] = isset($underwriting->created_at) ? $underwriting->created_at->format('Y-m-d H:i:s') : '';
            $rowData[4] = isset($underwriting->answers['weight'], $underwriting->answers['height']) 
                          ? round(($underwriting->answers['weight'] / $underwriting->answers['height'] / $underwriting->answers['height']) * 10000) 
                          : '';

            // Add answers to their respective columns

			/*

			foreach ($underwriting->answers['answers'] as $answerId) {
                if (isset($answer_title_location[$answerId])) {
                    $answerCol = $answer_title_location[$answerId] ; // Adjust for zero-based index
                    $rowData[$answerCol] = $answer['answer_title'] ?? ''; // Safeguard against undefined index
                }
            }
*/
			foreach ($groupedAnswers as $question => $answers) {
				foreach ($answers as $answer) {
					if (in_array($answer['id'], $underwriting->answers['answers'])) {
						$col = $answer_title_location[$answer['id']]-1;
						$rowData[$col] = $answer['answer_title'] ?? '';
					}
				}
			}



            fputcsv($output, $rowData);
        }

        fclose($output);
        exit;
    } catch (\Exception $e) {
        \Log::error('Exception caught', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);
        return response()->json(['error' => 'An error occurred while processing your request'], 500);
    }
}





 function getUwbyParant($parent_uws_id)
{
	// fetch all from UM where parent_uws_id 
return	Uw::
		where('parent_uws_id', '=', $parent_uws_id)
		->select('uws.id', 'uws.title as answer_title', 'uws.parent_uws_id','uws.group_id')
		->get()->toArray();
	
}

function cleanUtf8($string) {
    return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
}


function getIndividuals($id=0)
{
	return	Individual::
	where('id', '=', $id)
	->select('name', 'nric')
	->first();
}

public function readJson()
{
    $path = resource_path('json/uwgroup.json');

    if (File::exists($path)) {
        $jsonContent = File::get($path);
        $dataArray = json_decode($jsonContent, true);

        // Now, $dataArray contains the data from your JSON file
        return  $dataArray;
    } else {
        return [];
    }
}

public function prodUserDetails (Request $request) {


    $coverages = DB::table('coverages')
        ->join('individuals', 'coverages.owner_id', '=', 'individuals.id')
        ->join('users', 'individuals.user_id', '=', 'users.id')
        ->select(
            'coverages.owner_id',
            'users.ref_no as RefNo',
            'coverages.ref_no as CGNumber',
            'coverages.payment_term',
            DB::raw('(SELECT name FROM individuals WHERE coverages.owner_id = individuals.id) as Owner'),
            DB::raw('(SELECT name FROM individuals WHERE coverages.payer_id = individuals.user_id) as Payor'),
            DB::raw('(SELECT name FROM individuals WHERE coverages.covered_id = individuals.id) as Covered'),
            DB::raw('(SELECT type FROM individuals WHERE coverages.owner_id = individuals.id) as type'),
            DB::raw('(SELECT mobile FROM individuals WHERE coverages.payer_id = individuals.user_id) as PayorMobile'),
            'coverages.product_name as Product',
            'coverages.status as CoverageStatus',
            'coverages.payment_annually as Premium',
            'coverages.first_payment_on as FirstPaymentAt',
            'coverages.last_payment_on as LastPaymentAt'
        )
        ->whereIn('coverages.status', [
            'active',
            'active-increased',
            'deactivating',
            'grace-unpaid',
            'terminate'
        ])
        ->orWhere(function ($query) {
            $query->where('coverages.status', '=', 'unpaid')
                  ->whereNull('individuals.mobile');
        })
        ->orderBy('coverages.owner_id')
        ->get();
         
        // dd($coverages);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       
    
        $sheet->setCellValue('A1', 'Owner ID');
        $sheet->setCellValue('B1', 'Ref No');
        $sheet->setCellValue('C1', 'CG Number');
        $sheet->setCellValue('D1', 'Payment Term');
        $sheet->setCellValue('E1', 'Owner');
        $sheet->setCellValue('F1', 'Payor');
        $sheet->setCellValue('G1', 'Covered');
        $sheet->setCellValue('H1', 'Type');
        $sheet->setCellValue('I1', 'Payor Mobile');
        $sheet->setCellValue('J1', 'Product');
        $sheet->setCellValue('K1', 'Coverage Status');
        $sheet->setCellValue('L1', 'Premium');
        $sheet->setCellValue('M1', 'First Payment At');
        $sheet->setCellValue('N1', 'Last Payment At');
    
    
    
        $row = 2;
        foreach ($coverages as $coverage) {
            $sheet->setCellValue('A' . $row, $coverage->owner_id);
            $sheet->setCellValue('B' . $row, $coverage->RefNo);
            $sheet->setCellValue('C' . $row, $coverage->CGNumber);
            $sheet->setCellValue('D' . $row, $coverage->payment_term);
            $sheet->setCellValue('E' . $row, $coverage->Owner);
            $sheet->setCellValue('F' . $row, $coverage->Payor);
            $sheet->setCellValue('G' . $row, $coverage->Covered);
            $sheet->setCellValue('H' . $row, $coverage->type);
            $sheet->setCellValue('I' . $row, $coverage->PayorMobile);
            $sheet->setCellValue('J' . $row, $coverage->Product);
            $sheet->setCellValue('K' . $row, $coverage->CoverageStatus);
            $sheet->setCellValue('L' . $row, $coverage->Premium);
            $sheet->setCellValue('M' . $row, $coverage->FirstPaymentAt);
            $sheet->setCellValue('N' . $row, $coverage->LastPaymentAt);
            $row++;
        }
    
    
        $writer = new Xlsx($spreadsheet);
        
        
        header('X-Vapor-Base64-Encode:true');
        header('Content-Type: application/vnd.ms-excel');
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="coverages.xlsx"');
        // header('Cache-Control: max-age=0');
        
    
        $writer->save('php://output');
        // exit();
    
        }

public function insurancepenetrationReport(Request $request) {

            try {
                $this->validate($request, [
                    'date_from'     => 'required|date|date_format:Y/m/d|before_or_equal:to',
                    'date_to'       => 'required|date|date_format:Y/m/d'
                ]);
    
                $datas = DB::table('coverages')
                ->join('individuals', 'coverages.owner_id', '=', 'individuals.id')
                ->select(
                    'coverages.owner_id',
                    'individuals.name',
                    DB::raw("CASE 
                        WHEN individuals.has_other_life_insurance = '0' THEN 'No' 
                        WHEN individuals.has_other_life_insurance = '1' THEN 'Yes' 
                        ELSE 'Unknown' 
                    END AS has_life_insurance"),
                    DB::raw('MIN(coverages.first_payment_on) AS first_payment_on')
                )
                ->whereIn('individuals.has_other_life_insurance', ['0', '1'])
                ->where('coverages.first_payment_on', '>=', $request->input('date_from') . ' 00:00:00')
                ->where('coverages.first_payment_on', '<', $request->input('date_to') . ' 00:00:00')
                ->where('coverages.state', 'active')
                ->groupBy('coverages.owner_id', 'individuals.name', 'individuals.has_other_life_insurance')
                ->orderBy('first_payment_on')
                ->get();
    
    
                   $spreadsheet = new Spreadsheet();
                   $sheet = $spreadsheet->getActiveSheet();
    
                   $sheet->setCellValue('A1', 'Owner ID');
                   $sheet->setCellValue('B1', 'Name');
                   $sheet->setCellValue('C1', 'Has Life Insurance');
                   $sheet->setCellValue('D1', 'First Payment on');
    
    
                   $row = 2;
                   foreach ($datas as $data) {
                       $sheet->setCellValue('A' . $row, $data->owner_id);
                       $sheet->setCellValue('B' . $row, $data->name);
                       $sheet->setCellValue('C' . $row, $data->has_life_insurance);
                       $sheet->setCellValue('D' . $row, $data->first_payment_on);
                       $row++;
                   }
    
                   $writer = new Xlsx($spreadsheet);
        
        
                   header('X-Vapor-Base64-Encode:true');
                   header('Content-Type: application/vnd.ms-excel');
                //    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                   header('Content-Disposition: attachment; filename="Insurance Penetration Report.xlsx"');
                //    header('Cache-Control: max-age=0');
                   
               
                   $writer->save('php://output');
                //    exit();
    
            } catch (\Exception $e) {
                echo 'Expection :';
                dd($e->getMessage());
            }
    
        }

public function produserdetails_new(Request $request){

		$userlist=User::where('type','individual')->orderby ('id','asc')->get();



		$spreadsheet = new Spreadsheet();

				$sheet =$spreadsheet->getActiveSheet();

				$sheet->setcellvalue('A1','Name');
				$sheet->setcellvalue('B1','Email');
				$sheet->setcellvalue('C1','Mobile No');
				$sheet->setcellvalue('D1','Sign-up date');
				$sheet->setcellvalue('E1','Medical');
				$sheet->setcellvalue('F1','CI');
				$sheet->setcellvalue('G1','Accident');
				$sheet->setcellvalue('H1','Disability');
				$sheet->setcellvalue('I1','Death');

		$row=2;

		foreach($userlist as $ul){


			$individual_details=Individual::where('user_id',$ul->id)->first();

			if(!empty($individual_details)){

			$username=$individual_details->name;
			$email=$ul->email;
			$mobile_no=$individual_details->mobile;
			$sign_up_date=carbon::parse($individual_details->updated_at)->format('Y-m-d');
			
		    $coverages=Coverage::where('owner_id',$individual_details->id)->whereIn('status',[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DEACTIVATE])->get();


			$sheet->setcellvalue('A'.$row ,$username);
			$sheet->setcellvalue('B'.$row ,$email);
			$sheet->setcellvalue('C'.$row ,$mobile_no);
			$sheet->setcellvalue('D'.$row ,$sign_up_date);


			foreach($coverages as $cove){
				$date1 =date_create(carbon::parse($cove->first_payment_on)->format('Y-m-d'));
				//$date1 = new DateTime("2023-10-15");
				$date2 = date_create(carbon::parse(now())->format('Y-m-d'));

// Calculate the difference between the two dates
				$interval = ($date1->diff($date2));

// Get the year difference
				$years = $interval->y;


			 	// if($cove->renewal_date!=null){
			// 	$next_payment_on =$cove->renewal_date;
			 // }else{
			  // 	$next_payment_on =$cove->next_payment_on;
			   // }


			  if($years>=2){				
				   $Expiry_dates=carbon::parse($cove->next_payment_on)->addMonths(3);
			   }else{
				   $Expiry_dates=carbon::parse($cove->next_payment_on)->addMonth();
			     }


				if($cove->product_name=='Death'){

				$sheet->setcellvalue('I'.$row,$Expiry_dates);

				}elseif($cove->product_name=='Medical'){

					$sheet->setcellvalue('E'.$row,$Expiry_dates);
				}elseif($cove->product_name=='Critical Illness'){

					$sheet->setcellvalue('F'.$row,$Expiry_dates);
				}elseif($cove->product_name=='Disability'){

					$sheet->setcellvalue('H'.$row,$Expiry_dates);
				}else{

					$sheet->setcellvalue('G'.$row,$Expiry_dates);
				}

				

			}
			
			$row++;
		
	}
}
	
		$output=new Xlsx($spreadsheet);
		header('X-Vapor-Base64-Encode:true');
		header('Content-Type: application/vnd.ms-excel');
	 //    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Production_user_details.xlsx"');
	 //    header('Cache-Control: max-age=0');
		
		$output->save('php://output');


		}
		

}