<?php     

namespace App\Http\Livewire\Credit;

use App\Credit;
use Livewire\Component;

class Sum extends Component
{
    public $user;

    public function render()
    {
        $sum = Credit::where('user_id',$this->user->id)->sum('amount');
        $user = $this->user;
        return view('livewire.credit.sum',compact('sum','user'));
    }
}
