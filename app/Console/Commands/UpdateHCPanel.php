<?php     

namespace App\Console\Commands;

use App\HCPanel;
use Illuminate\Console\Command;

class UpdateHCPanel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update HC-Clinic Information';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
//        $this->line('Try to fetch new data from www.hcsb.com.my ...');
        $data = @file_get_contents("https://www.hcsb.com.my/services/servicedt.asmx/GetHCPanelClinics");
//        $this->line('Parsing Data ...');
        $data = json_decode($data);
        if(is_array($data) && count($data) > 0){
            $this->line('Inserting data ...');
//            $bar = $this->output->createProgressBar(count($data));
//            $bar->start();
            foreach ($data as $hc_clinic){

                $clinic = HCPanel::where("code",$hc_clinic->ProviderCode)->first();
                if(empty($clinic))
                    $clinic = new HCPanel();
                if(empty($hc_clinic->ProviderCode)) {
//                    $bar->advance();
                    continue;
                }

                $clinic->type = $hc_clinic->ProviderType ?? '';
                $clinic->code = $hc_clinic->ProviderCode ?? '';
                $clinic->reg_no = $hc_clinic->ProviderRegNo ?? '';
                $clinic->name = $hc_clinic->ProviderName ?? '';
                $clinic->address = $hc_clinic->ProviderAddress ?? '';
                $clinic->city = $hc_clinic->ProviderCity ?? '';
                $clinic->state = $hc_clinic->ProviderState ?? '';
                $clinic->post_code = $hc_clinic->ProviderPostCode ?? '';
                $clinic->phone = $hc_clinic->ProviderPhone ?? '';
                $clinic->fax = $hc_clinic->ProviderFax ?? '';
                $clinic->email = json_encode(explode(";",$hc_clinic->ProviderEmail ?? ''));
                $clinic->contact_name = $hc_clinic->ProviderContactName ?? '';
                $clinic->doctor_name = $hc_clinic->ProviderDoctorName ?? '';
                $clinic->bank_account = $hc_clinic->ProviderBankAccount ?? '';
                $clinic->billing_name = $hc_clinic->ProviderBillingName ?? '';
                $clinic->billing_address = $hc_clinic->ProviderBillingAddress ?? '';
                $clinic->latitude = $hc_clinic->ProviderLatitude ?? '';
                $clinic->longitude = $hc_clinic->ProviderLongitude ?? '';
                $clinic->save();
//                $bar->advance();

            }
//            $bar->finish();
//            $this->info('HC-Clinics data updated Successfully !');
        }else{
//            $this->error('Cannot parse data!');
        }
    }
}
