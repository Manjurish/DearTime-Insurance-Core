<?php     
namespace Database\Seeders;

use App\Imports\JobImport;
use App\Industry;
use App\IndustryJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class IndustryJobsSeeder extends Seeder
{
    function run()
    {
        if (($handle = fopen(resource_path('imports/DearTimeOccupation.csv'), "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1100, ",")) !== FALSE) {
                $columns = count($data);

                if($row >= 2){
                    for ($column = 0; $column < $columns; $column++) {

                        $industry = Industry::where("name",$data[0]);

                        if($industry->count() == 0) {
                            $industry = new Industry();
                            $industry->name = trim($data[0]);
                            $industry->name_bm = trim($data[1]);
                            $industry->name_ch = trim($data[2]);
                            $industry->save();
                        }else{
                            $industry = $industry->first();
                        }

                        $industry_job = IndustryJob::where("name",$data[3])->where("industry_id",$industry->id);

                        if($industry_job->count() == 0) {
                            $industry_job = new IndustryJob();
                            $industry_job->industry_id = $industry->id;
                            $industry_job->name = trim($data[3]);
                            $industry_job->name_bm = trim($data[4]);
                            $industry_job->name_ch = trim($data[5]);
                            $industry_job->gender = strtolower(trim($data[6] ?? null));
                            $industry_job->death = $this->parse($data[7]);
                            $industry_job->Accident = $this->parse($data[8]);
                            $industry_job->TPD = $this->parse($data[9]);
                            $industry_job->Medical = $this->parse($data[10]);
                            $industry_job->save();
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }
    }

    private function parse($value)
    {
        if(empty($value))
            return 0;
        if($value == 'D')
            return -1;
        return $value;
    }
}
