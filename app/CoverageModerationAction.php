<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverageModerationAction extends Model
{
    use Uuids;

    /*protected $fillable = [
        'individual_id',
        'product_id',
        'created_by',
        'action ',
        'uuid'
    ];*/
    protected $table = 'coverage_moderation_action';
}
