<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class UwsSio extends Model
{
    protected $table    =   'uws_sio';

    public $incrementing = FALSE;

	public function question()
	{
		return $this->belongsTo(UwsGroupSio::class, 'group_id');
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
		return $this->hasMany(UwsSio::class, 'parent_uws_id');
	}

}
