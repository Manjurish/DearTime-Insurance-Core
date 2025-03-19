<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpoCharityFunds extends Model
{ 
    use Uuids;
    //protected $table = 'sop_funds';
    protected $casts = ['flcancel_credit' => 'array'];

    
}
