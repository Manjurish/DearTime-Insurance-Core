<?php     

namespace App\Http\Controllers\Admin;

use App\Helpers;
use App\Helpers\Helper;
use App\Industry;
use App\IndustryJob;
use App\InternalUser;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;


class JobController extends BaseController
{
    public function configure()
    {
        $this->model = IndustryJob::class;
        $this->addBladeSetting("side",true);
        $this->setTitle("Job");
        $this->addColumn("Name",'name');
        $this->addColumn("Industry",'industry_idToTEXT');
        $this->addColumn("Gender",'is_male');

        $this->addField("name","Name",'required');
        $this->addField("industry_id","Industry",'required','select','',Industry::get()->pluck('name','id')->toArray());
        $this->addField("gender","Gender",'required','select','',['Male'=>'Male','Female'=>'Female']);
        $this->addField("death","Death",'required|numeric');
        $this->addField("Accident","Accident",'required|numeric');
        $this->addField("TPD","TPD",'required|numeric');
        $this->addField("Medical","Medical",'required|numeric');


        $this->addAction('admin.Job.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.Job.destroy','<i class="feather icon-trash-2"></i>','Delete',['$uuid'],Helpers::getAccessControlMethod(),['class'=>'ask']);

        return $this;

    }

}
