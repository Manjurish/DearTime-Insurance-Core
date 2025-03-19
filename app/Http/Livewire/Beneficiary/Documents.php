<?php     

namespace App\Http\Livewire\Beneficiary;

use App\Beneficiary;
use App\Document;
use Livewire\Component;

class Documents extends Component
{
    protected $listeners = ['refreshNominee' => '$refresh'];

    public $nominee;
    public function render()
    {
        return view('livewire.beneficiary.documents');
    }

    public function deleteDoc($document){
        $doc=json_decode($document,true);
        Document::where('url',$doc['url'])->delete();
        $this->emit('refreshNominee');
    }

    public function refreshNominee()
    {
        $this->nominee = Beneficiary::where('id',$this->nominee->id)->first();
    }
}
