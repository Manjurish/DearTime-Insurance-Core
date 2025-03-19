<?php

namespace App\Http\Livewire\Underwritings;

use App\Helpers\Enum;
use App\Underwriting;
use App\Uw;
use App\UwGroup;

use Livewire\Component;

class Underwritings extends Component
{
    public    $underwriting;
    public    $answers;
    protected $listeners   = ['show'];
    public function render()
    {
        return view('livewire.underwritings.underwritings');
    }
    public function show($uuid)
    {
        $underwriting = Underwriting::whereUuid($uuid)->first();
        $this->underwriting = $underwriting;

        $answers = $underwriting->answers;
        // dd($answers['answers']);

        if ($answers['answers'] ?? NULL) {
            $ans = collect($answers['answers'])->map(
                function ($id) {
                    $uw = \App\Uw::find($id);
                    return [
                        'question' => $uw->question->Title,
                        'answer' => $uw->Title
                    ];
                }
            );
            $answers['answers'] = $ans->toArray();
            $this->answers = $answers;
        }
    }
}