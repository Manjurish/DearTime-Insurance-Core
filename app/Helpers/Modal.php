<?php     

namespace App\Helpers;

class Modal
{
    public static function success($class, $title){
        $class->dispatchBrowserEvent('swal:modal', [
            'type'  => 'success',
            'title' => $title,
            'icon'  => 'success'
        ]);
    }

	public static function error($class, $title){
		$class->dispatchBrowserEvent('swal:modal', [
			'type'  => 'error',
			'title' => $title,
			'icon'  => 'success'
		]);
	}
}
