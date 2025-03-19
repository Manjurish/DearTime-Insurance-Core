<?php     

namespace App\Http\Controllers\Admin;

use App\Helpers;
use App\Helpers\Helper;
use App\Industry;
use App\InternalUser;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mmeshkatian\Ariel\BaseController;


class IndustryController extends BaseController
{
    public function configure()
    {
        $this->model = Industry::class;
        $this->setTitle("Industry");
        $this->addBladeSetting("side",true);
        $this->addColumn("Name",'name');

        $this->addField("name","Name",'required');

        $this->addAction('admin.Industry.edit','<i class="feather icon-edit-2"></i>','Edit',['$uuid'],Helpers::getAccessControlMethod());
        $this->addAction('admin.Industry.destroy','<i class="feather icon-trash-2"></i>','Delete',['$uuid'],Helpers::getAccessControlMethod(),['class'=>'ask']);


        return $this;

    }

}
