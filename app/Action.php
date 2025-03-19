<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;
    use UuidsRefs;

    public $prefix = 'AC';

    protected $casts = [
        'actions' => 'array',
    ];

    protected $fillable = [
      'user_id', 'actions','parent_id','type','event','previous_action_id','status','execute_on','due_date','approved_on','approved_by','createdby_type','createdby_id','created_at','updated_at'
    ];

    public function coverages()
    {
        return $this->belongsToMany(Coverage::class)->withTimestamps();;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by_id');
    }

    public function particularChanges()
    {
        return $this->hasMany(ParticularChange::class);
    }
    public function createdbyable()
    {
        return $this->morphTo();
    }
}
