<?php     
namespace Database\Seeders;

use App\ClaimQuestion;

use App\ClaimQuestionAnswer;
use App\ForeignQuestion;
use App\ForeignQuestionAnswer;
use Illuminate\Database\Seeder;


class ForeignQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $questions = [
            1=>[
                'title'=>'Any other nationalities you have acquired?',
                'title_bm'=>'Any other nationalities you have acquired?',
                'title_ch'=>'Any other nationalities you have acquired?',
                'type'=>'select',
                'content'=>[
                    1=>[
                        'title'=>'YES',
                        'title_bm'=>'YES',
                        'title_ch'=>'YES',
                    ],
                    2=>[
                        'title'=>'NO',
                        'title_bm'=>'NO',
                        'title_ch'=>'NO',
                    ],
                ]
            ],
            2=>[
                'title'=>'Please list all nationalities you have acquired.',
                'title_bm'=>'Please list all nationalities you have acquired.',
                'title_ch'=>'Please list all nationalities you have acquired.',
                'type'=>'select',
                'data'=>['hide'=>[1=>[2]],'source'=>'nationalities','fieldName'=>'nationality'],

            ],
            3=>[
                'title'=>'Have you been residing in Malaysia for past 6 months?',
                'title_bm'=>'Have you been residing in Malaysia for past 6 months?',
                'title_ch'=>'Have you been residing in Malaysia for past 6 months?',
                'type'=>'select',
                'content'=>[
                    3=>[
                        'title'=>'YES',
                        'title_bm'=>'YES',
                        'title_ch'=>'YES',
                    ],
                    4=>[
                        'title'=>'NO',
                        'title_bm'=>'NO',
                        'title_ch'=>'NO',
                    ],
                ]
            ],
            4=>[
                'title'=>'Do you plan to travel or reside in another country (other than Malaysia) for more than 3 months?',
                'title_bm'=>'Do you plan to travel or reside in another country (other than Malaysia) for more than 3 months?',
                'title_ch'=>'Do you plan to travel or reside in another country (other than Malaysia) for more than 3 months?',
                'type'=>'select',
                'content'=>[
                    5=>[
                        'title'=>'YES',
                        'title_bm'=>'YES',
                        'title_ch'=>'YES',
                    ],
                    6=>[
                        'title'=>'NO',
                        'title_bm'=>'NO',
                        'title_ch'=>'NO',
                    ],
                ]
            ],
            5=>[
                'title'=>'Please confirm your status',
                'title_bm'=>'Please confirm your status',
                'title_ch'=>'Please confirm your status',
                'type'=>'select',
                'content'=>[
                    7=>[
                        'title'=>'Working in Malaysia with valid working permit',
                        'title_bm'=>'Working in Malaysia with valid working permit',
                        'title_ch'=>'Working in Malaysia with valid working permit',
                    ],
                    8=>[
                        'title'=>'Studying in Malaysia with valid student pass',
                        'title_bm'=>'Studying in Malaysia with valid student pass',
                        'title_ch'=>'Studying in Malaysia with valid student pass',
                    ],
                    9=>[
                        'title'=>'Own a business in Malaysia',
                        'title_bm'=>'Own a business in Malaysia',
                        'title_ch'=>'Own a business in Malaysia',
                    ],
                    10=>[
                        'title'=>'Married to Malaysian',
                        'title_bm'=>'Married to Malaysian',
                        'title_ch'=>'Married to Malaysian',
                    ],
                    11=>[
                        'title'=>'Malaysia My Second Home Program (MM2H)',
                        'title_bm'=>'Malaysia My Second Home Program (MM2H)',
                        'title_ch'=>'Malaysia My Second Home Program (MM2H)',
                    ],
                    12=>[
                        'title'=>'Dependent to individual who meet one of the five criteria above',
                        'title_bm'=>'Dependent to individual who meet one of the five criteria above',
                        'title_ch'=>'Dependent to individual who meet one of the five criteria above',
                    ],
                ]
            ],
            6 => [
                'title'=>'Working permit showing duration of validity',
                'title_bm'=>'Working permit showing duration of validity',
                'title_ch'=>'Working permit showing duration of validity',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[8,9,10,11,12]]],
            ],
            7 => [
                'title'=>'Student pass showing duration of validity',
                'title_bm'=>'Student pass showing duration of validity',
                'title_ch'=>'Student pass showing duration of validity',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[7,9,10,11,12]]],
            ],
            8 => [
                'title'=>'Business registration document showing your name',
                'title_bm'=>'Business registration document showing your name',
                'title_ch'=>'Business registration document showing your name',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[7,8,10,11,12]]],
            ],
            9 => [
                'title'=>'Marriage certificate',
                'title_bm'=>'Marriage certificate',
                'title_ch'=>'Marriage certificate',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[7,8,9,11,12]]],
            ],
            10 => [
                'title'=>'MM2H visa showing duration of validity',
                'title_bm'=>'MM2H visa showing duration of validity',
                'title_ch'=>'MM2H visa showing duration of validity',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[7,8,9,10,12]]],
            ],
            11 => [
                'title'=>'Dependent pass/visa showing duration of validity',
                'title_bm'=>'Dependent pass/visa showing duration of validity',
                'title_ch'=>'Dependent pass/visa showing duration of validity',
                'type'=>'upload',
                'data'=>['hide'=>[5=>[7,8,9,10,11]]],
            ],
            12 => [
                'title'=>'Valid until',
                'title_bm'=>'Valid until',
                'title_ch'=>'Valid until',
                'type'=>'date',
                'data'=>['hide'=>[5=>[9,10]]],
            ],

        ];

        ForeignQuestion::query()->truncate();
        ForeignQuestionAnswer::query()->truncate();

        foreach ($questions as $key=>$item) {
                $foreign_question = new ForeignQuestion();
                $foreign_question->id = $key;
                $foreign_question->title = $item['title'];
                $foreign_question->title_bm = $item['title_bm'];
                $foreign_question->title_ch = $item['title_ch'];
                $foreign_question->type = $item['type'];
                $foreign_question->data = json_encode($item['data'] ?? []);
                $foreign_question->save();

                if(!empty($item['content']) && is_array($item['content'])){
                    foreach ($item['content'] as $keyi=>$i) {

                        $foreign_question_answer = new ForeignQuestionAnswer();
                        $foreign_question_answer->answer_id = $keyi;
                        $foreign_question_answer->question_id = $foreign_question->id;
                        $foreign_question_answer->title = $i['title'];
                        $foreign_question_answer->title_bm = $i['title_bm'];
                        $foreign_question_answer->title_ch = $i['title_ch'];
                        $foreign_question_answer->save();

                    }

                }
            }

    }
}
