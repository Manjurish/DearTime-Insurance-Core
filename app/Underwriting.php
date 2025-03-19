<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Traits\Encryptable;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Model;

class Underwriting extends Model
{
    use UuidsRefs,Encryptable;

    public $prefix = 'UW';
    protected $guarded = [];
    protected $visible = ['death', 'disability', 'ci', 'medical', 'updated_at'];
    protected $casts = ['answers' => 'array'];
 
   protected $encryptable = ['answers'];

    public function individual(){
        return $this->belongsTo(Individual::class)->WithChild();
    }

    public function coverages(){
        return $this->hasMany(Coverage::class,'uw_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function canBuyCoverage()
    {
        return ($this->death || $this->disability ||  $this->ci || $this->medical);
    }
}
