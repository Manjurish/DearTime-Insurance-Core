<?php      
 // ALL RIGHTS RESERVED ®️ DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomerReport extends Model
{
   
    protected $table = 'view_customer_report';
    
   // protected $primaryKey = 'user_ref';
    public $timestamps = false;

    public function getRegisterAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d/m/Y H:i A') : null;
    }

}