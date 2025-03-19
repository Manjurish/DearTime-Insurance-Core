<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use App\Traits\UuidsRefs;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use UuidsRefs;
    public $prefix = 'TX';
    protected $guarded = [];
    protected $hidden = ['id'];

	//protected $dateFormat = 'd/m/Y H:i';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getAmountAttribute()
    {
        return number_format($this->attributes['amount'],2);
    }

	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format(config('static.datetime_format'));
	}

	/*public function getDateAttribute($value)
	{
		return Carbon::parse($value)->format(config('static.datetime_format'));
	}*/
}
