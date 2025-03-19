<?php     

namespace App\Http\Controllers\Admin;

use App\Claim;
use App\ClaimStatusLogs;
use App\Coverage;
use App\Helpers;
use App\User;
use App\Individual;
use App\Helpers\Enum;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\InternalUser;
use App\TPAClaim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mmeshkatian\Ariel\BaseController;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class ClaimController extends Controller
{
	public function index()
	{
		return view('admin.claims.list');
	}
	public function configure()
	{
		$this->model = Claim::class;
		$this->setTitle("Claim");
		$this->addColumn("User Name",'OwnerName');
		$this->addColumn("Policy",'PolicyName');
		$this->addColumn("Status",'status');
		$this->addColumn("Date",function ($q) {
			return Carbon::parse($q->created_at)->format('d/m/Y H:i A');
		});
		$this->addBladeSetting('hideCreate',TRUE);

		$this->addField("","User Name",'','view',function ($data,$value) {
			return $value->OwnerName ?? '';
		},[],'','',TRUE);
		$this->addField("","Policy Name",'','view',function ($data,$value) {
			return $value->PolicyName ?? '';
		},[],'','',TRUE);
		$this->addField("","Policy Coverage",'','view',function ($data,$value) {
			return number_format($value->coverage->coverage ?? 0,2) . ' RM';
		},[],'','',TRUE);
		$this->addField("","Uploaded Documents",'','view',function ($data,$value) {
			$out = '<div class="row">';
			foreach ($value->documents ?? [] as $document) {
				$out .= '<div class="col-md-3"><img src=' . $document->thumbLink . ' class="m-1" style="width:100%"/></div>';
			}
			$out .= "</div>";
			return $out;
		},[],'','',TRUE);
		$this->addField("status","Change Status",'required','select','',config('static.status_list'));
		$this->addAction('admin.claim.edit','<i class="feather icon-clipboard"></i>','Review',['$uuid'],Helpers::getAccessControlMethod());

		return $this;

	}

	public function edit($id, Request $request)
	{
		$claim = Claim::whereUuid($id)->first();
		
		if ($request->input('claim_status') != null && $request->input('claim_status') != '') {

		   $this->validate($request, [
					'claim_status' => 'in:draft,notified,pending for os document,pending for approval,approved,settled,rejected,closed,ex-gratia,cancelled',
				]);

			$uuid = $claim->uuid;
			$claim->status = $request->input('claim_status');
			$claim->save();
			$c = new ClaimStatusLogs();
			$c->claim_id = $claim->id;
			$c->status = $claim->status;
			$c->uuid = $claim->uuid;
			$c->updated_by=InternalUser::where('id',auth('internal_users')->id())->first()->name;
			$c->save();
			return redirect()->route('admin.claim.edit', $uuid)->with("success_alert","Claims status update");
		}

		
		$coverage = Coverage::find($claim->coverage_id);
		$docs = $this->getDocs($coverage);
		$ans = $claim->answers()->get();
		$docs = $claim->documents()->get();
		$statusChanges = $claim->statusChanges()->get();
        $claimst1 = 'cancelled';

		if ($docs != null) {
			foreach ($docs as $k => $doc) {
				if ($doc->name != 'consent.pdf') {
					$tmp = explode('.', $doc->path);
					$doc->ext =  end($tmp);
				}
			}
		}
		return view('admin.claim',compact('claim','docs', 'ans', 'docs', 'claimst1', 'statusChanges'));
	}

	public function cancel(Request $request)
	{
		$input = $request->input();
		$claim = Claim::where('id',$input['id'])->first();
		
			$uuid = $claim->uuid;
			$claim->status ='cancelled';
			$claim->save();
			$c = new ClaimStatusLogs();
			$c->claim_id = $claim->id;
			$c->status = $claim->status;
			$c->uuid = $claim->uuid;
			$c->updated_by=InternalUser::where('id',auth('internal_users')->id())->first()->name;
			$c->reason = $request->input('description');
			$c->save();
			return redirect()->route('admin.claim.edit', $uuid)->with("success_alert","Claims status updated");
		
	}

	public function import()
	{
		$breadcrumbs = [
			['name' => 'Admin Area','link' => route('admin.dashboard.main')],
			['name' => __('web/messages.claim_data_import'),'link' => url()->current()],
		];

		return view('admin.claims.index',compact('breadcrumbs'));
	}

	public function importCsv(Request $request)
	{
		$inputFileName = $request->file('file')->getRealPath();
		$reader        = new Csv();
		$spreadsheet   = $reader->load($inputFileName);
		$sheetData     = $spreadsheet->getActiveSheet()->toArray();
		$duplicateRow  = [];
		$addedRow      = 0;

		if(!empty($sheetData)){
			foreach ($sheetData as $data) {
				$input['claim_type'] = $data[0];
				$input['claim_no']   = $data[1];
				$countClaimNo        = TPAClaim::where('claim_no',$input['claim_no'])->count();
				if($countClaimNo > 0){
					array_push($duplicateRow,$input['claim_no']);
					continue;
				}
				$input['policy_no']             = $data[2];
				$input['id_no']                 = $data[3];
				$input['date_of_visit']         = Carbon::parse($data[4])->format('Y-m-d');
				$input['date_of_discharge']     = Carbon::parse($data[5])->format('Y-m-d');
				$input['diagnosis_code_1']      = $data[6];
				$input['diagnosis_code_2']      = $data[7];
				$input['diagnosis_code_3']      = $data[8];
				$input['provider_code']         = $data[9];
				$input['provider_name']         = $data[10];
				$input['provider_invoice_no']   = $data[11];
				$input['date_claim_received']   = Carbon::parse($data[12])->format('Y-m-d');
				$input['medical_leave_from']    = Carbon::parse($data[13])->format('Y-m-d');
				$input['medical_leave_to']      = Carbon::parse($data[14])->format('Y-m-d');
				$input['tpa_invoice_no']        = $data[15];
				$input['cliam_type']            = $data[16];
				$input['actual_invoice_amount'] = $data[17];
				$input['approved_amount']       = $data[18];
				$input['non_approved_amount']   = $data[19];

				TPAClaim::create($input);
				$addedRow++;
			}
		}

		return redirect()->back()->with(['duplicateRow' => array_unique($duplicateRow),'addedRow' => $addedRow]);
	}

	/**
	 * @param $coverage
	 * @return array|void
	 */
	private function getDocs($coverage)
	{
		$death_docs = [
			['name' => 'APS- Death','link' => resource_path('documents/claims/death/1._DS_-_Death_for_PH_20201017.xlsm')],
			['name' => 'Post Mortem','link' => NULL],
			['name' => 'Toxicology Report','link' => NULL],
		];

		$disability_docs = [
			['name' => 'APS- Disability','link' => resource_path('documents/claims/disability/1._DS_-_Disability_for_PH_20201017.xlsm')],
			['name' => 'Other','link' => NULL],
		];

		$ciAllFiles  = array_diff(scandir(resource_path('documents/claims/ci')),array("..","."));
		natsort($ciAllFiles);
		$ci_docs = [];

		foreach ($ciAllFiles as $filename) {
			array_push($ci_docs,
					   [
						   'name' => $filename,
						   'link' => resource_path('documents/claims/ci/' . $filename)
					   ]);
					   
	    $med_docs = [
		    ['name' => $filename],
		    ['name' => 'Other','link' => NULL],
	    ];
						   
	}

		if($coverage->product_name == Enum::PRODUCT_NAME_DISABILITY){
			$docs = $disability_docs;
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_DEATH || $coverage->product_name == Enum::PRODUCT_NAME_ACCIDENT){
			$docs = $death_docs;
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_CRITICAL_ILLNESS){
			$docs = $ci_docs;
		}elseif($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
			$docs = $med_docs;
		}

		return $docs;
	}
	
	public function exportAnswers($id, Request $request)
	{
		try {
			$claim 	= 	Claim::whereUuid($id)->first();
			$ans 	= 	$claim->answers()->get();

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet       = $spreadsheet->getActiveSheet();

			$claim3 = Coverage::where('id',$claim->coverage_id)->first()->product_name;
			$claim8 = $claim->created_at->format('d-m-Y H:i:s');

			$user = User::where('id',$claim->owner_id)->first();
			
			if ($claim3 =="Accident"){
					if($claim->individual_id == $claim->owner_id){
						$claim3 = 'Accidental Disability';
			}else{
				$claim3 = 'Accidental Death';
			}
		}

			$arrHeading = [

						"Claimant's Questions ".$claim3, 
						
					];

					$styleArray = [
						'font' => [
							'bold' => true,
						],
						'borders' => [
							'outline' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
							],
						],
					];
					$sheet->getStyle('B2:E2')->applyFromArray($styleArray);
					$sheet->mergeCells('B2:E2');
					$sheet->getStyle('B2:E2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle('B2:E2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
					$sheet->getRowDimension('2')->setRowHeight(27.8);
					$sheet->getStyle('B2:E2')->getFont()->setSize(16);
					$sheet->fromArray([$arrHeading], NULL, 'B2');
		
		$arrHeading = [

				'Name of Insured:', 
				
			];

			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];
			$sheet->getStyle('B4')->applyFromArray($styleArray);
			$sheet->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'B4');

			$arrHeading = [

				$claim->OwnerName,
				
			];
			
			$sheet->getStyle('C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'C4');


			$arrHeading = [

				'Name of Claimant:', 
				
			];
			
			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];
			$sheet->getStyle('B6')->applyFromArray($styleArray);
			$sheet->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'B6');

			$arrHeading = [

				$claim->ClaimantName,
				
			];
			$sheet->getStyle('C6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'C6');

			$arrHeading = [

				'Insured NRIC No:', 
				
			];
			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];
			$sheet->getStyle('D4')->applyFromArray($styleArray);
			$sheet->getStyle('D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'D4');
			
			$claim1 =Individual::where('id',$claim->individual_id)->first()->nric;
			
			$claim2 = Individual::where('id',$claim->owner_id)->first()->nric;


			$arrHeading = [

				$claim2,
				
			];
			$sheet->getStyle('E4')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
			$sheet->getStyle('E4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'E4');

			$arrHeading = [

				'Claimant NRIC No', 
				
			];
			
			$styleArray = [
				'font' => [
					'bold' => true,
				],
			];
			$sheet->getStyle('D6')->applyFromArray($styleArray);
			$sheet->getStyle('D6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'D6');
			
			$arrHeading = [

				$claim1,
				
			];
			$sheet->getStyle('E6')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
			$sheet->getStyle('E6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$sheet->fromArray([$arrHeading], NULL, 'E6');

			if ($claim3 =="Accidental Disability"){
					$arrHeadings	=	[
						'S.No',
						'Question',
						'Answer'
					];
					$styleArray =  [
						'borders' => [
							'outline' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '000000'],
							],
						],
						
						'font' => [
							'bold' => true,
						],
					];
					$sheet->getStyle('B8')->applyFromArray($styleArray);
					$sheet->getStyle('C8')->applyFromArray($styleArray);
					$sheet->mergeCells('D8:E8');
					$sheet->getStyle('D8:E8')->applyFromArray($styleArray);

					$sheet->fromArray([$arrHeadings], NULL, 'B8');

					$rowCount = 10;
					foreach ($ans as $key => $answer) {
						$result				=	[];
						$result[]			=	($key + 1);
						$result[]			=	$answer->title;
						$result[]			=	$answer->value;

						$styleArray = [
							'borders' => [
								'outline' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => '000000'],
								],
							],
						];
						$sheet->getStyle('B'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('C'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('D'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('E'.$rowCount)->applyFromArray($styleArray);

						$sheet->getDefaultColumnDimension('C'.$rowCount)->setWidth(15);
						$sheet->getDefaultColumnDimension('D'.$rowCount)->setWidth(15);
						$sheet->getDefaultColumnDimension('E'.$rowCount)->setWidth(15);

						$sheet->getDefaultRowDimension('C'.$rowCount)->setRowHeight(29);
						$sheet->getDefaultRowDimension('D'.$rowCount)->setRowHeight(29);
						$sheet->getDefaultRowDimension('E'.$rowCount)->setRowHeight(29);

						$sheet->mergeCells('D10:E10');
						$sheet->mergeCells('D11:E11');
						$sheet->mergeCells('D12:E12');
						$sheet->mergeCells('D13:E13');
						$sheet->mergeCells('D14:E14');
						$sheet->mergeCells('D15:E15');
						$sheet->mergeCells('D16:E16');
						$sheet->mergeCells('D17:E17');
						$sheet->mergeCells('D18:E18');
						$sheet->mergeCells('D19:E19');
						$sheet->mergeCells('D20:E20');
						$sheet->mergeCells('D21:E21');
						$sheet->mergeCells('D22:E22');
						$sheet->mergeCells('D23:E23');
						$sheet->mergeCells('D24:E24');
						$sheet->mergeCells('D25:E25');
						$sheet->mergeCells('D26:E26');
						$sheet->mergeCells('D27:E27');
						$sheet->mergeCells('D28:E28');
						$sheet->mergeCells('D29:E29');

						$sheet->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('D'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('E'.$rowCount)->getAlignment()->setWrapText(true);

						$spreadsheet->getDefaultStyle()->getFont()->setSize(11);

						$sheet->fromArray([$result], NULL, 'B'.$rowCount);
						$rowCount++;
					}

					$arrHeading	=	[
						'This document is digitally signed by the claimant using biometric facial recognition on '.($claim8).'. No physical signature is required.',
						];
					
					$styleArray =  [
	
						'font' => [
							'bold' => true,
						],
					];
	
					$sheet->getStyle('B31')->applyFromArray($styleArray);
					$sheet->getStyle('B31')->getAlignment()->setWrapText(true);
					$sheet->mergeCells('B31:J31');
					$sheet->fromArray([$arrHeading], NULL, 'B31');
			
				}

				if ($claim3 =="Accidental Death"){
					$arrHeadings	=	[
						'S.No',
						'Question',
						'Answer'
					];
					$styleArray =  [
						'borders' => [
							'outline' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '000000'],
							],
						],
						
						'font' => [
							'bold' => true,
						],
					];
					$sheet->getStyle('B8')->applyFromArray($styleArray);
					$sheet->getStyle('C8')->applyFromArray($styleArray);
					$sheet->mergeCells('D8:E8');
					$sheet->getStyle('D8:E8')->applyFromArray($styleArray);
		
					$sheet->fromArray([$arrHeadings], NULL, 'B8');
		
					$rowCount = 10;
					foreach ($ans as $key => $answer) {
						$result				=	[];
						$result[]			=	($key + 1);
						$result[]			=	$answer->title;
						$result[]			=	$answer->value;
		
						$styleArray = [
							'borders' => [
								'outline' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => '000000'],
								],
							],
						];
						 $sheet->getStyle('B'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('C'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('D'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('E'.$rowCount)->applyFromArray($styleArray);
		
						 $sheet->getDefaultColumnDimension('C'.$rowCount)->setWidth(15);
						 $sheet->getDefaultColumnDimension('D'.$rowCount)->setWidth(15);
						 $sheet->getDefaultColumnDimension('E'.$rowCount)->setWidth(15);
		
		
						 $sheet->getDefaultRowDimension('C'.$rowCount)->setRowHeight(29);
						 $sheet->getDefaultRowDimension('D'.$rowCount)->setRowHeight(29);
						 $sheet->getDefaultRowDimension('E'.$rowCount)->setRowHeight(29);
		
						 $sheet->mergeCells('D10:E10');
						 $sheet->mergeCells('D11:E11');
						 $sheet->mergeCells('D12:E12');
						 $sheet->mergeCells('D13:E13');
						 $sheet->mergeCells('D14:E14');
						 $sheet->mergeCells('D15:E15');
						 $sheet->mergeCells('D16:E16');
						 $sheet->mergeCells('D17:E17');
						 $sheet->mergeCells('D18:E18');
						 $sheet->mergeCells('D19:E19');
						 $sheet->mergeCells('D20:E20');
						 $sheet->mergeCells('D21:E21');
						 $sheet->mergeCells('D22:E22');
						 $sheet->mergeCells('D23:E23');
						 $sheet->mergeCells('D24:E24');
						 $sheet->mergeCells('D25:E25');
						 $sheet->mergeCells('D26:E26');
		
						$sheet->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('D'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('E'.$rowCount)->getAlignment()->setWrapText(true);
		
						$spreadsheet->getDefaultStyle()->getFont()->setSize(11);
		
						$sheet->fromArray([$result], NULL, 'B'.$rowCount);
						$rowCount++;

					}

					$arrHeading	=	[
						'This document is digitally signed by the claimant using biometric facial recognition on '.($claim8).'. No physical signature is required.',
						];
					
					$styleArray =  [
	
						'font' => [
							'bold' => true,
						],
					];
						 $sheet->getStyle('B27')->applyFromArray($styleArray);
						 $sheet->getStyle('B27')->getAlignment()->setWrapText(true);
						 $sheet->mergeCells('B27:J27');
						 $sheet->fromArray([$arrHeading], NULL, 'B27');
			    }

		if ($claim3 =="Death"){
					$arrHeadings	=	[
						'S.No',
						'Question',
						'Answer'
					];
					$styleArray =  [
						'borders' => [
							'outline' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '000000'],
							],
						],
						
						'font' => [
							'bold' => true,
						],
					];
					$sheet->getStyle('B8')->applyFromArray($styleArray);
					$sheet->getStyle('C8')->applyFromArray($styleArray);
					$sheet->mergeCells('D8:E8');
					$sheet->getStyle('D8:E8')->applyFromArray($styleArray);
		
					$sheet->fromArray([$arrHeadings], NULL, 'B8');
		
					$rowCount = 10;
					foreach ($ans as $key => $answer) {
						$result				=	[];
						$result[]			=	($key + 1);
						$result[]			=	$answer->title;
						$result[]			=	$answer->value;
		
						$styleArray = [
							'borders' => [
								'outline' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => '000000'],
								],
							],
						];
						 $sheet->getStyle('B'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('C'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('D'.$rowCount)->applyFromArray($styleArray);
						 $sheet->getStyle('E'.$rowCount)->applyFromArray($styleArray);
		
						 $sheet->getDefaultColumnDimension('C'.$rowCount)->setWidth(15);
						 $sheet->getDefaultColumnDimension('D'.$rowCount)->setWidth(15);
						 $sheet->getDefaultColumnDimension('E'.$rowCount)->setWidth(15);
		
		
						 $sheet->getDefaultRowDimension('C'.$rowCount)->setRowHeight(29);
						 $sheet->getDefaultRowDimension('D'.$rowCount)->setRowHeight(29);
						 $sheet->getDefaultRowDimension('E'.$rowCount)->setRowHeight(29);
		
						 $sheet->mergeCells('D10:E10');
						 $sheet->mergeCells('D11:E11');
						 $sheet->mergeCells('D12:E12');
						 $sheet->mergeCells('D13:E13');
						 $sheet->mergeCells('D14:E14');
						 $sheet->mergeCells('D15:E15');
						 $sheet->mergeCells('D16:E16');
						 $sheet->mergeCells('D17:E17');
						 $sheet->mergeCells('D18:E18');
						 $sheet->mergeCells('D19:E19');
						 $sheet->mergeCells('D20:E20');
						 $sheet->mergeCells('D21:E21');
						 $sheet->mergeCells('D22:E22');
						 $sheet->mergeCells('D23:E23');
						 $sheet->mergeCells('D24:E24');
						 $sheet->mergeCells('D25:E25');
						 $sheet->mergeCells('D26:E26');
		
						$sheet->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('D'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('E'.$rowCount)->getAlignment()->setWrapText(true);
		
						$spreadsheet->getDefaultStyle()->getFont()->setSize(11);
		
						$sheet->fromArray([$result], NULL, 'B'.$rowCount);
						$rowCount++;
					}

					$arrHeading	=	[
						'This document is digitally signed by the claimant using biometric facial recognition on '.($claim8).'. No physical signature is required.',
						];
					
					$styleArray =  [
						'font' => [
							'bold' => true,
						],
					];
	
						 $sheet->getStyle('B27')->applyFromArray($styleArray);
						 $sheet->getStyle('B27')->getAlignment()->setWrapText(true);
						 $sheet->mergeCells('B27:J27');
						 $sheet->fromArray([$arrHeading], NULL, 'B27');
			
			    }
		
		if ($claim3 =="Critical Illness"){
						$arrHeadings	=	[
							'S.No',
							'Question',
							'Answer'
						];
						$styleArray =  [
							'borders' => [
								'outline' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => '000000'],
								],
							],
							
							'font' => [
								'bold' => true,
							],
						];
						$sheet->getStyle('B8')->applyFromArray($styleArray);
						$sheet->getStyle('C8')->applyFromArray($styleArray);
						$sheet->mergeCells('D8:E8');
						$sheet->getStyle('D8:E8')->applyFromArray($styleArray);
			
						$sheet->fromArray([$arrHeadings], NULL, 'B8');
			
						$rowCount = 10;
						foreach ($ans as $key => $answer) {
							$result				=	[];
							$result[]			=	($key + 1);
							$result[]			=	$answer->title;
							$result[]			=	$answer->value;
			
							$styleArray = [
								'borders' => [
									'outline' => [
										'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
										'color' => ['argb' => '000000'],
									],
								],
							];
							$sheet->getStyle('B'.$rowCount)->applyFromArray($styleArray);
							$sheet->getStyle('C'.$rowCount)->applyFromArray($styleArray);
							$sheet->getStyle('D'.$rowCount)->applyFromArray($styleArray);
							$sheet->getStyle('E'.$rowCount)->applyFromArray($styleArray);
			
							$sheet->getDefaultColumnDimension('C'.$rowCount)->setWidth(15);
							$sheet->getDefaultColumnDimension('D'.$rowCount)->setWidth(15);
							$sheet->getDefaultColumnDimension('E'.$rowCount)->setWidth(15);
			
			
							$sheet->getDefaultRowDimension('C'.$rowCount)->setRowHeight(29);
							$sheet->getDefaultRowDimension('D'.$rowCount)->setRowHeight(29);
							$sheet->getDefaultRowDimension('E'.$rowCount)->setRowHeight(29);
			
							$sheet->mergeCells('D10:E10');
							$sheet->mergeCells('D11:E11');

							$sheet->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
							$sheet->getStyle('D'.$rowCount)->getAlignment()->setWrapText(true);
							$sheet->getStyle('E'.$rowCount)->getAlignment()->setWrapText(true);
			
							$spreadsheet->getDefaultStyle()->getFont()->setSize(11);
			
							$sheet->fromArray([$result], NULL, 'B'.$rowCount);
							$rowCount++;
						}

				$arrHeading	=	[
							'This document is digitally signed by the claimant using biometric facial recognition on '.($claim8).'. No physical signature is required.',
							];
						
						$styleArray =  [
							'font' => [
								'bold' => true,
							],
						];
		
							 $sheet->getStyle('B13')->getAlignment()->setWrapText(true);
							 $sheet->getStyle('B13')->applyFromArray($styleArray);
							 $sheet->mergeCells('B13:J13');
							 $sheet->fromArray([$arrHeading], NULL, 'B13');
				}

		if ($claim3 =="Disability"){
					$arrHeadings	=	[
						'S.No',
						'Question',
						'Answer'
					];
					$styleArray =  [
						'borders' => [
							'outline' => [
								'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
								'color' => ['argb' => '000000'],
							],
						],
						'font' => [
							'bold' => true,
						],
					];
					$sheet->getStyle('B8')->applyFromArray($styleArray);
					$sheet->getStyle('C8')->applyFromArray($styleArray);
					$sheet->mergeCells('D8:E8');
					$sheet->getStyle('D8:E8')->applyFromArray($styleArray);

					$sheet->fromArray([$arrHeadings], NULL, 'B8');

					$rowCount = 10;
					foreach ($ans as $key => $answer) {
						$result				=	[];
						$result[]			=	($key + 1);
						$result[]			=	$answer->title;
						$result[]			=	$answer->value;

						$styleArray = [
							'borders' => [
								'outline' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => '000000'],
								],
							],
						];
						$sheet->getStyle('B'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('C'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('D'.$rowCount)->applyFromArray($styleArray);
						$sheet->getStyle('E'.$rowCount)->applyFromArray($styleArray);

						$sheet->getDefaultColumnDimension('C'.$rowCount)->setWidth(15);
						$sheet->getDefaultColumnDimension('D'.$rowCount)->setWidth(15);
						$sheet->getDefaultColumnDimension('E'.$rowCount)->setWidth(15);


						$sheet->getDefaultRowDimension('C'.$rowCount)->setRowHeight(29);
						$sheet->getDefaultRowDimension('D'.$rowCount)->setRowHeight(29);
						$sheet->getDefaultRowDimension('E'.$rowCount)->setRowHeight(29);

						$sheet->mergeCells('D10:E10');
						$sheet->mergeCells('D11:E11');
						$sheet->mergeCells('D12:E12');
						$sheet->mergeCells('D13:E13');
						$sheet->mergeCells('D14:E14');
						$sheet->mergeCells('D15:E15');
						$sheet->mergeCells('D16:E16');
						$sheet->mergeCells('D17:E17');
						$sheet->mergeCells('D18:E18');
						$sheet->mergeCells('D19:E19');
						$sheet->mergeCells('D20:E20');
						$sheet->mergeCells('D21:E21');
						$sheet->mergeCells('D22:E22');
						$sheet->mergeCells('D23:E23');
						$sheet->mergeCells('D24:E24');
						$sheet->mergeCells('D25:E25');
						$sheet->mergeCells('D26:E26');
						$sheet->mergeCells('D27:E27');
						$sheet->mergeCells('D28:E28');
						$sheet->mergeCells('D29:E29');

						$sheet->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('D'.$rowCount)->getAlignment()->setWrapText(true);
						$sheet->getStyle('E'.$rowCount)->getAlignment()->setWrapText(true);

						$spreadsheet->getDefaultStyle()->getFont()->setSize(11);

						$sheet->fromArray([$result], NULL, 'B'.$rowCount);
						$rowCount++;
					}

				$arrHeading	=	[
						'This document is digitally signed by the claimant using biometric facial recognition on '.($claim8).'. No physical signature is required.',
						];
					
					$styleArray =  [
	
						'font' => [
							'bold' => true,
						],
					];
	
						 $sheet->getStyle('B31')->applyFromArray($styleArray);
						 $sheet->getStyle('B31')->getAlignment()->setWrapText(true);
						 $sheet->mergeCells('B31:J31');
						 $sheet->fromArray([$arrHeading], NULL, 'B31');
	            }

			$filename = 'Claim-Question-Answers-'.date('d-m-y-' . substr((string)microtime(), 1, 8)) . ".xlsx";

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
}