<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use Illuminate\Database\Eloquent\Model;

class Uw extends Model
{
	public $incrementing = FALSE;

	public function question()
	{
		return $this->belongsTo(UwGroup::class, 'group_id');
	}

	public function getTitleAttribute()
	{
		if (!app()->getLocale()) {
			return $this->attributes['title'];
		}

		$locale = app()->getLocale();

		if (empty($locale) || $locale == 'en') {
			return $this->attributes['title'];
		}

		if ($locale == 'ch') {
			$locale = 'zh';
		}

		return $this->attributes['title_' . $locale];
	}

	public function getInfoAttribute()
	{
		if (!app()->getLocale()) {
			return $this->attributes['info'];
		}

		$locale = app()->getLocale();

		if (empty($locale) || $locale == 'en') {
			return $this->attributes['info'];
		}

		if ($locale == 'ch') {
			$locale = 'zh';
		}

		return $this->attributes['info_' . $locale];
	}

	public function sub_questions()
	{
		return $this->hasMany(Uw::class, 'parent_uws_id');
	}
}