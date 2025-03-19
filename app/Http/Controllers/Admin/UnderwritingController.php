<?php

namespace App\Http\Controllers\Admin;

use App\Coverage;
use App\Http\Controllers\Controller;
use App\Underwriting;
use App\Uw;
use Carbon\Carbon;
use Mmeshkatian\Ariel\BaseController;

class UnderwritingController extends Controller
{
    public function index()
    {
        return view('admin.underwriting.index');
    }

    public function configure()
    {
        $this->model = Underwriting::class;
        $this->setTitle("Underwriting");

        $this->addColumn("RefNo", 'ref_no');
        $this->addColumn("User", function ($q) {
            return '<img src="' . ($q->individual->selfie ?? '') . '" style="width:50px;height:50px" />' . ' ' . ($q->individual->name ?? '');
        });
        $this->addColumn("CreatedBy", function ($q) {
            return $q->creator->profile->name ?? '';
        });
        $this->addColumn("Answers", function ($q) {
            $answers = $q->answers;
            $out = '';
            $out .= "Smoke :" . ($answers['smoke'] ?? '-') . "<br>";
            $out .= "Height :" . ($answers['height'] ?? '-') . "<br>";
            $out .=  "Weight :" . ($answers['weight'] ?? '-') . "<br>";
            return $out;
        });
        $this->addColumn("Date", function ($q) {
            return Carbon::parse($q->created_at)->format('d/m/Y H:i A');
        });

        $this->addColumn("Allowed Products", function ($q) {
            $out = '';
            if ($q->death == '1')
                $out .= 'Death<br>';
            if ($q->disability == '1')
                $out .= 'Disability<br>';
            if ($q->ci == '1')
                $out .= 'Critical Illness<br>';
            if ($q->medical == '1')
                $out .= 'Medical<br>';

            return $out;
        });

        $this->addBladeSetting('hideCreate', true);

        return $this;
    }

    //dev-498 - underwriting - show popup of survey qns and ans
    public function show($uuid)
    {
        $underwriting = Underwriting::whereUuid($uuid)->first();
        // $this->underwriting = $underwriting;

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

                    // dump($uw->question->Title);
                    // $uwg = Uw::find($uw->group_id);

                    // return [$uw->Title,];
                    // return [$uw->Title];
                }
            );
            $answers['answers'] = $ans->toArray();
        }
        dd($answers);
        // $this->answers = $answers;
    }
}