<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SpoHouseholdMembers extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $with    = ['occupation','industry'];

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }
	public function occupation()
	{
		return $this->belongsTo(IndustryJob::class,'occupation','id');
	}
    public function industry()
    {
        return $this->belongsTo(Industry::class,'industry','id');
    }
}
