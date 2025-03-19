<?php     

namespace App\Http\Controllers\Api;

use App\CharityApplicant;
use App\Document;
use App\Helpers;
use Illuminate\Http\Request;
use PhpParser\Comment\Doc;
use App\Http\Controllers\Controller;


class CharityApplicantController extends Controller
{
    private $proof_of_income_docs = [
        'Income Tax Form',
    ];

    public function apply(Request $request)
    {

        //check if user is eligible to apply charity

        if ($request->user()->profile->household_income > 3000)
            return ['status' => 'error', 'message' => __('web/messages.charity_household_error')];

        $request->validate([
            'about_self' => 'required|string',
            'files.*' => 'required|mimes:jpg,jpeg,png,bmp,pdf|max:5000',
            'selfie' => 'mimes:jpg,jpeg,png,bmp|max:5000',
            'sponsor_thank_note' => 'string|max:100',
            'dependants' => 'required|integer|min:0|max:10'
        ]);

        //   if(CharityApplicant::where('individual_id', $request->user()->profile->id)->first() != null)
        //        return ['status' => 'error', 'message' => 'You have already applied for charity program!'];

        //return $request->user()->profile->charity;

        $charity = CharityApplicant::updateOrCreate(['individual_id' => $request->user()->profile->id, 'about_self' => $request->about_self, 'sponsor_thank_note' => $request->sponsor_thank_note, 'dependants' => $request->dependants]);
        $doc = [];
        foreach ($request->file('files') as $file) {
            $doc[] = Helpers::crateDocumentFromUploadedFile($file, $charity, 'salary_proof');
        }

        $selfie = null;
        if ($request->has('selfie'))
            $selfie = Helpers::crateDocumentFromUploadedFile($request->file('selfie'), $charity, 'selfie');


        // upload files here

        return ['status' => 'success', 'message' => __('web/messages.charity_applied'), 'data' => ['files' => $doc, 'selfie' => $selfie]];

    }


    public function queueList(Request $request)
    {
      $charities = CharityApplicant::where("active","1")->oldest()->paginate(5);
        return ['status' => 'success', 'data' => $charities];

    }
}
