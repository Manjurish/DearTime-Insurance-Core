<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticularChange extends Model
{
    use Uuids;

    protected $table = 'particular_changes';

    public function createdBy()
    {
        return $this->belongsTo(InternalUser::class, 'created_by');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
