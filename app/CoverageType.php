<?php  

namespace App;

use App\Helpers\Enum;
use App\Traits\Uuids;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;


class CoverageType extends Model implements Auditable
{
    use Uuids;
    use \OwenIt\Auditing\Auditable;

   
    protected $table = 'coverages';

    protected $auditColumns = ['payment_term_new'];      
    
    }


