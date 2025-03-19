<?php     

namespace App\Http\Controllers\Api;


use App\Country;
use App\ForeignQuestion;
use App\ForeignQuestionAnswer;
use App\Helpers;
use App\Individual;
use App\UserForeignAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class ForeignController extends Controller
{
    public function getQuestions(Request $request)
    {
        $user = $request->user();
        $sources = [
            'nationalities'=>Country::select('uuid','nationality','is_allowed')->get()
        ];
        $questions = ForeignQuestion::get();
        $out = [];
        foreach ($questions as $question) {
            $ans = [];
            foreach ($question->answers as $answer) {
                $ans[] = ['id'=>$answer->answer_id,'title'=>$answer->title];
            }
            $out[] = ['id'=>$question->id,'title'=>$question->title,'type'=>$question->type,'data'=>json_decode($question->data),'content'=>$ans];
        }
        return [
            'status' => 'success',
            'data'=>[
                'questions'=>$out,
                'sources'=>$sources,
            ]
        ];
    }

    public function store(Request $request)
    {
        //validate questions
        $user = $request->user();

        UserForeignAnswer::where("user_id",$user->id)->delete();

        foreach ($request->input('questions') ?? [] as $i=>$item) {
            $ans = new UserForeignAnswer();
            $ans->user_id = $user->id;
            $ans->question_id = $i;
            $ans->answer = is_string($item) ? $item : json_encode($item);
            $ans->save();
        }
        foreach ($request->file('questions_files') ?? [] as $i=>$item) {
            $ans = new UserForeignAnswer();
            $ans->user_id = $user->id;
            $ans->question_id = $i;
            $ans->answer = 'upload';
            $ans->save();
            Helpers::crateDocumentFromUploadedFile($item, $ans);
        }

        $ans_1_2_1 = UserForeignAnswer::where("user_id",$user->id)->where("question_id","1")->first()->answer ?? '2';
        $ans_1_2_1_1 = UserForeignAnswer::where("user_id",$user->id)->where("question_id","2")->first()->answer ?? null;

        $country_allowed = Country::whereUuid($ans_1_2_1_1)->first()->is_allowed ?? false;
        $cond_1 = ($ans_1_2_1 == '1' && $country_allowed);

        $ans_1_2_2 = UserForeignAnswer::where("user_id",$user->id)->where("question_id","3")->first()->answer ?? '4';
        $cond_2 = $ans_1_2_2 == '4';

        $ans_1_2_3 = UserForeignAnswer::where("user_id",$user->id)->where("question_id","4")->first()->answer ?? '6';
        $cond_3 = $ans_1_2_3 == '5';


        $ans_valid_until = UserForeignAnswer::where("user_id",$user->id)->where("question_id","12")->first()->answer ?? null;
        if(!empty($ans_valid_until) && $ans_valid_until != 'null'){
            $valid_until = Carbon::createFromFormat("d/m/Y",$ans_valid_until);
            $diff = $valid_until->diffInYears(now());
            $cond_4 = $diff < 1;
        }else
            $cond_4 = false;

        $declined = (!$cond_1 || !$cond_2 || !$cond_3 || !$cond_4);

        $profile = $user->profile;
        if(!empty($profile)){
            $profile->is_restricted_foreign = $declined ? '1' : '0';
            $profile->save();
        }

        return [
            'status'=>'success',
            'data'=>[
                'answers'=>[
                    '$ans_1_2_1'=>$ans_1_2_1,
                    '$ans_1_2_1_1'=>$ans_1_2_1_1,
                    '$country_allowed'=>$country_allowed,
                    '$cond_1'=>$cond_1,
                    '$ans_1_2_2'=>$ans_1_2_2,
                    '$cond_2'=>$cond_2,
                    '$ans_1_2_3'=>$ans_1_2_3,
                    '$cond_3'=>$cond_3,
                    '$cond_4'=>$cond_3,
                ],
                'accept'=> !$declined,
                'msg'=>$declined ? 'Sorry, we are unable to cover you right now. Thank you for your interest.' : 'Data saved successfully',
            ]
        ];

    }
}
