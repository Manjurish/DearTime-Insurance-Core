<?php     
namespace Database\Seeders;

use App\ClaimQuestion;

use App\ClaimQuestionAnswer;
use Illuminate\Database\Seeder;


class ClaimQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $disability = [
            1 => ['title'=>'Occupation before disability','title_bm'=>'Sebelum Hilang Upaya Pekerjaan','title_ch'=>'残疾前 职业','type'=>'free_select','content'=>
              [
                  1 => ['title'=>'data|occ_name','title_bm'=>'data|occ_name','title_ch'=>'data|occ_name'],
              ]
            ],
            2 => ['title'=>'Your monthly income before disability','title_bm'=>'Sebelum Hilang Upaya Pendapatan bulanan','title_ch'=>'残疾前 月收入','type'=>'price','data'=>['value'=>'income']],
            3 => ['title'=>'Name & address of business or employer before disability','title_bm'=>'Sebelum Hilang Upaya Nama dan alamat perniagaan atau majikan','title_ch'=>'残疾前 公司或雇主的名称和地址','type'=>'text','data'=>['hide'=>[1=>[1,2,3,4,5]]]],
            4 => ['title'=>'Before Disability Contact No.','title_bm'=>'Sebelum Hilang Upaya No. Telefon','title_ch'=>'残疾前 联络号码','type'=>'phone','data'=>['hide'=>[1=>[1,2,3,4,5]]]],

            5 => ['title'=>'Current Occupation','title_bm'=>'Status Pekerjaan Sekarang Pekerjaan','title_ch'=>'目前就业状况 职业','type'=>'free_select','content'=>
                [
                    1 => ['title'=>'data|occ_name','title_bm'=>'data_occ_name','title_ch'=>'data_occ_name'],
                ]
            ],
            6 => ['title'=>'Current monthly income','title_bm'=>'Status Pekerjaan Sekarang Pendapatan bulanan','title_ch'=>'目前就业状况 月收入','type'=>'price','data'=>['value'=>'income']],
            7 => ['title'=>'Current Employment Status Name and address of business or employer','title_bm'=>'Status Pekerjaan Sekarang Nama dan alamat perniagaan atau majikan','title_ch'=>'目前就业状况 公司或雇主的名称和地址','type'=>'text','data'=>['hide'=>[5=>[1,2,3,4,5]]]],
            8 => ['title'=>'Contact no. of current business or employer','title_bm'=>'Status Pekerjaan Sekarang No. Telefon','title_ch'=>'目前就业状况 联络号码','type'=>'phone','data'=>['hide'=>[5=>[1,2,3,4,5]]]],


            9 => ['title'=>'Last working day (if you are not currently employed) ','title_bm'=>'Tarikh kerja terakhir (jika anda kini todak bekerja)','title_ch'=>'最后工作日期（如果您目前没有工作）','type'=>'date','data'=>['hide'=>[5=>[1,2,3,4]]]],
            10 => ['title'=>'Are you able to work in any other occupation? If no, is there any aspect of the disability that will prevent you from doing so?','title_bm'=>'Adakah anda dapat bekerja dalam pekerjaan lain? Sekiranya tidak, adakah terdapat aspek kecacatan yang akan menghalang anda daripada berbuat demikian?','title_ch'=>'您还能从事其他职业吗？如果不是，那么是否有任何残疾方面会阻止您这样做？','type'=>'free_select','contents'=>[
                1 => ['title'=>'Yes','title_bm'=>'Ya','title_ch'=>'是'],
                2 => ['title'=>'No','title_bm'=>'TIDAK','title_ch'=>'否'],
            ]],
            11 => ['title'=>'Are you medically boarded out (MBO)?','title_bm'=>'Adakan anda diberhentikan kerja atas alasan kesihatan (MBO)? ','title_ch'=>'您是否因健康的理由而停止工作 (MBO)？','type'=>'select','content'=>[
                1 => ['title'=>'No','title_bm'=>'TIDAK','title_ch'=>'否'],
                2 => ['title'=>'YES. Please submit the following','title_bm'=>'Ya, sila lampirkan berikut','title_ch'=>'是, 请提交以下'],
            ]],
            12 => ['title'=>'Letter from Employer, SOCSO','title_bm'=>'Surat dari Majikan, PERKESO ','title_ch'=>'雇主或SOCSO的信','type'=>'upload','data'=>['hide'=>[11=>[1]]]],
            13 => ['title'=>'Medical report from MBO','title_bm'=>'Laporan perubatan dari MBO','title_ch'=>'MBO的医疗报告','type'=>'upload','data'=>['hide'=>[11=>[1]]]],
            14 => ['title'=>'The DISABILITY is due to','title_bm'=>'HILANG UPAYA adalah disebabkan','title_ch'=>'残疾是由于','type'=>'select','content'=>[
                1 => ['title'=>'Accident','title_bm'=>'Kemalangan','title_ch'=>'意外'],
                2 => ['title'=>'Illness','title_bm'=>'Penyakit','title_ch'=>'疾病'],
            ]],
            15 => ['title'=>'Please provide police report','title_bm'=>'Sila lampirkan laporan polis','title_ch'=>'请提交警方报告','type'=>'upload','data'=>['hide'=>[14=>[2]]]],
//            16 => ['title'=>'Please provide the full details of any other insurance policies which you may receive payment for this condition?','title_bm'=>'Sila berikan butir-butir penuh mengenai sebarang polisi insurans lain yang anda boleh terima bayaran untuk keadaan ini?','title_ch'=>'请提供此情况下您可能会收到任何其他保单付款的完整详情','type'=>'text'],

            17 => ['title'=>'I/We confirm that the answers given are true and accurate','title_bm'=>'Saya/Kami mengesahkan bahawa jawapan yang diberikan adalah benar dan tepat	','title_ch'=>'本人/我们确认给于的答案是真实及准确的','type'=>'checkbox','data'=>['required'=>true]],
            18 => ['title'=>'I/We understand that DearTime\'s acceptance of this form is not admission of DearTime\'s liability of my/our claim','title_bm'=>'Saya/Kami memahami bahawa penerimaan borang oleh DearTime tidak boleh dianggap sebagai penerimaan liabiliti ke atas tuntutan yang dibuat','title_ch'=>'本人/我们了解，DearTime接受此表格并非代表DearTime对我/我们的索赔承担责任','type'=>'checkbox','data'=>['required'=>true]],
//            19 => ['title'=>'I/We authorise any institution or individual that has any records or knowledge of my/our health and medical history to disclose such information to DearTime or its representative	','title_bm'=>'Saya/Kami memberi kuasa kepada mana-mana institusi atau individu yang mempunyai rekod atau maklumat tentang kesihatan dan sejarah perubatan saya/kami untuk mendedahkannya kepada DearTime atau wakilnya.','title_ch'=>'本人/我们授权任何拥有我/我们的健康和病史记录或知识的机构或个人向DearTime或其代表披露此类信息','type'=>'checkbox','data'=>['required'=>true]],
//            20 => ['title'=>'I/We understand and agree that any personal information collected or held by DearTime (whether through this application or otherwise obtained) maybe used and disclosed by DearTime to individuals/institutions related to and associated with DearTime or any selected third party within or outside Malaysia such as reinsurers, claims investigation companies and industry associations to process the application. The information may also be used to provide service for this and other financial products and to communicate with me/us. I/We understand that I/we have a right to get access to and request for correction of any personal information held by DearTime. Such requests can be made at DearTime Customer Care.','title_bm'=>'Saya/Kami memahami dan bersetuju bahawa maklumat peribadi yang dikumpul atau dipegang oleh DearTime (sama ada melalui permohonan ini ataupun cara lain) boleh digunakan dan didedahkan kepada individu atau institusi yang berkaitan dengan DearTime atau mana-mana pihak ketiga di dalam atau di luar Malaysia seperti penanggung insurans semula (reinsurer), syarikat penyiasatan tuntutan dan persatuanindustri bagi memproses permohonan ini. Maklumat tersebut juga boleh digunakan untuk memberikan perkhidmatan ke atas permohonan ini dan juga produk kewangan lain. Saya/Kami memahami bahawa saya/kami mempunyai hak untuk mendapatkan dan memohon pembetulan dibuat ke atas mana-mana maklumat persendirian yang disimpan oleh DearTime. Permohonan tersebut boleh dibuat di Pusat Khidmat Pelanggan DearTime.','title_ch'=>'本人/我们理解并同意，DearTime可能会将收集或持有的任何个人信息（无论是通过本应用程序还是通过其他方式获取）使用和披露给与DearTime或与之相关的个人/机构或在马来西亚境内或境外的任何选定的第三方如再保险公司，索赔调查公司和行业协会来处理该申请。该信息还可用于为该金融产品和其他金融产品提供服务，并与我/我们进行通信。本人/我们了解，本人/我们有权访问并要求更正DearTime持有的任何个人信息。此类要求可向DearTime客户服务中心提出。','type'=>'checkbox','data'=>['required'=>true]],


        ];
        $death = [
            21 => ['title'=>'Cause of death','title_bm'=>'Punca kemation','title_ch'=>'死亡原因','type'=>'select','content'=>[
                1 => ['title'=>'Natural Death','title_bm'=>'Kematian Biasa','title_ch'=>'自然死亡'],
                2 => ['title'=>'Accident','title_bm'=>'Kemalangan','title_ch'=>'意外'],
                3 => ['title'=>'Suicide','title_bm'=>'Bunuh Diri','title_ch'=>'自杀'],
            ]],
            22 => ['title'=>'Police report','title_bm'=>'Laporan polis','title_ch'=>'警方报告','type'=>'upload','data'=>['hide'=>[21=>[1]]]],
            23 => ['title'=>'Post Mortem','title_bm'=>'Post Mortem ','title_ch'=>'Post Mortem ','type'=>'upload','data'=>['hide'=>[21=>[1]]]],
            24 => ['title'=>'Toxicology Reports','title_bm'=>'Toxicology Reports','title_ch'=>'Toxicology Reports','type'=>'upload','data'=>['hide'=>[21=>[1]]]],
            25 => ['title'=>'Other reports or certificate','title_bm'=>'Laporan atau sijil lain','title_ch'=>'其他报告或证明','type'=>'upload','data'=>['hide'=>[21=>[1]]]],

            26 => ['title'=>'Date of death','title_bm'=>'Tarikh kematian','title_ch'=>' 死亡日期','type'=>'date'],
            27 => ['title'=>'Time of death','title_bm'=>'Masa kematian','title_ch'=>'死亡时间','type'=>'time'],
            28 => ['title'=>'Place of death','title_bm'=>'Tempat kemation','title_ch'=>'死亡地点','type'=>'text'],

            29 => ['title'=>'I/We confirm that the answers given are true and accurate','title_bm'=>'Saya/Kami mengesahkan bahawa jawapan yang diberikan adalah benar dan tepat','title_ch'=>'本人/我们确认给于的答案是真实及准确的','type'=>'checkbox','data'=>['required'=>true]],
            30 => ['title'=>'I/We understand that DearTime\'s acceptance of this form is not admission of DearTime\'s liability of my/our claim	','title_bm'=>'Saya/Kami memahami bahawa penerimaan borang oleh DearTime tidak boleh dianggap sebagai penerimaan liabiliti ke atas tuntutan yang dibuat	','title_ch'=>'本人/我们了解，DearTime接受此表格并非代表DearTime对我/我们的索赔承担责任','type'=>'checkbox','data'=>['required'=>true]],
//            31 => ['title'=>'I/We authorise any institution or individual that has any records or knowledge of my/our health and medical history to disclose such information to DearTime or its representative','title_bm'=>'Saya/Kami memberi kuasa kepada mana-mana institusi atau individu yang mempunyai rekod atau maklumat tentang kesihatan dan sejarah perubatan saya/kami untuk mendedahkannya kepada DearTime atau wakilnya.','title_ch'=>'本人/我们授权任何拥有我/我们的健康和病史记录或知识的机构或个人向DearTime或其代表披露此类信息','type'=>'checkbox','data'=>['required'=>true]],
//            32 => ['title'=>'I/We understand and agree that any personal information collected or held by DearTime (whether through this application or otherwise obtained) maybe used and disclosed by DearTime to individuals/institutions related to and associated with DearTime or any selected third party within or outside Malaysia such as reinsurers, claims investigation companies and industry associations to process the application. The information may also be used to provide service for this and other financial products and to communicate with me/us. I/We understand that I/we have a right to get access to and request for correction of any personal information held by DearTime. Such requests can be made at DearTime Customer Care.','title_bm'=>'Saya/Kami memahami dan bersetuju bahawa maklumat peribadi yang dikumpul atau dipegang oleh DearTime (sama ada melalui permohonan ini ataupun cara lain) boleh digunakan dan didedahkan kepada individu atau institusi yang berkaitan dengan DearTime atau mana-mana pihak ketiga di dalam atau di luar Malaysia seperti penanggung insurans semula (reinsurer), syarikat penyiasatan tuntutan dan persatuanindustri bagi memproses permohonan ini. Maklumat tersebut juga boleh digunakan untuk memberikan perkhidmatan ke atas permohonan ini dan juga produk kewangan lain. Saya/Kami memahami bahawa saya/kami mempunyai hak untuk mendapatkan dan memohon pembetulan dibuat ke atas mana-mana maklumat persendirian yang disimpan oleh DearTime. Permohonan tersebut boleh dibuat di Pusat Khidmat Pelanggan DearTime.','title_ch'=>'本人/我们理解并同意，DearTime可能会将收集或持有的任何个人信息（无论是通过本应用程序还是通过其他方式获取）使用和披露给与DearTime或与之相关的个人/机构或在马来西亚境内或境外的任何选定的第三方如再保险公司，索赔调查公司和行业协会来处理该申请。该信息还可用于为该金融产品和其他金融产品提供服务，并与我/我们进行通信。本人/我们了解，本人/我们有权访问并要求更正DearTime持有的任何个人信息。此类要求可向DearTime客户服务中心提出。','type'=>'checkbox','data'=>['required'=>true]],

        ];
        $accident = [
            33 => ['title'=>'Cause of death','title_bm'=>'Punca kemation','title_ch'=>'死亡原因','type'=>'select','content'=>[
                1 => ['title'=>'Natural Death','title_bm'=>'Kematian Biasa','title_ch'=>'自然死亡'],
                2 => ['title'=>'Accident','title_bm'=>'Kemalangan','title_ch'=>'意外'],
                3 => ['title'=>'Suicide','title_bm'=>'Bunuh Diri','title_ch'=>'自杀'],
            ]],
            34 => ['title'=>'Death Certificate','title_bm'=>'Death Certificate_bm','title_ch'=>'Death Certificate_ch','type'=>'upload'],
            34 => ['title'=>'Police report','title_bm'=>'Laporan polis','title_ch'=>'警方报告','type'=>'upload','data'=>['hide'=>[33=>[1]]]],
            35 => ['title'=>'Post Mortem','title_bm'=>'Post Mortem ','title_ch'=>'Post Mortem ','type'=>'upload','data'=>['hide'=>[33=>[1]]]],
            36 => ['title'=>'Toxicology Reports','title_bm'=>'Toxicology Reports','title_ch'=>'Toxicology Reports','type'=>'upload','data'=>['hide'=>[33=>[1]]]],
            37 => ['title'=>'Other reports or certificate','title_bm'=>'Laporan atau sijil lain','title_ch'=>'其他报告或证明','type'=>'upload','data'=>['hide'=>[33=>[1]]]],

            38 => ['title'=>'Date of death','title_bm'=>'Tarikh kematian','title_ch'=>' 死亡日期','type'=>'date'],
            39 => ['title'=>'Time of death','title_bm'=>'Masa kematian','title_ch'=>'死亡时间','type'=>'time'],
            40 => ['title'=>'Place of death','title_bm'=>'Tempat kemation','title_ch'=>'死亡地点','type'=>'text'],

            41 => ['title'=>'I/We confirm that the answers given are true and accurate','title_bm'=>'Saya/Kami mengesahkan bahawa jawapan yang diberikan adalah benar dan tepat','title_ch'=>'本人/我们确认给于的答案是真实及准确的','type'=>'checkbox','data'=>['required'=>true]],
            42 => ['title'=>'I/We understand that DearTime\'s acceptance of this form is not admission of DearTime\'s liability of my/our claim	','title_bm'=>'Saya/Kami memahami bahawa penerimaan borang oleh DearTime tidak boleh dianggap sebagai penerimaan liabiliti ke atas tuntutan yang dibuat	','title_ch'=>'本人/我们了解，DearTime接受此表格并非代表DearTime对我/我们的索赔承担责任','type'=>'checkbox','data'=>['required'=>true]],
//            43 => ['title'=>'I/We authorise any institution or individual that has any records or knowledge of my/our health and medical history to disclose such information to DearTime or its representative','title_bm'=>'Saya/Kami memberi kuasa kepada mana-mana institusi atau individu yang mempunyai rekod atau maklumat tentang kesihatan dan sejarah perubatan saya/kami untuk mendedahkannya kepada DearTime atau wakilnya.','title_ch'=>'本人/我们授权任何拥有我/我们的健康和病史记录或知识的机构或个人向DearTime或其代表披露此类信息','type'=>'checkbox','data'=>['required'=>true]],
//            44 => ['title'=>'I/We understand and agree that any personal information collected or held by DearTime (whether through this application or otherwise obtained) maybe used and disclosed by DearTime to individuals/institutions related to and associated with DearTime or any selected third party within or outside Malaysia such as reinsurers, claims investigation companies and industry associations to process the application. The information may also be used to provide service for this and other financial products and to communicate with me/us. I/We understand that I/we have a right to get access to and request for correction of any personal information held by DearTime. Such requests can be made at DearTime Customer Care.','title_bm'=>'Saya/Kami memahami dan bersetuju bahawa maklumat peribadi yang dikumpul atau dipegang oleh DearTime (sama ada melalui permohonan ini ataupun cara lain) boleh digunakan dan didedahkan kepada individu atau institusi yang berkaitan dengan DearTime atau mana-mana pihak ketiga di dalam atau di luar Malaysia seperti penanggung insurans semula (reinsurer), syarikat penyiasatan tuntutan dan persatuanindustri bagi memproses permohonan ini. Maklumat tersebut juga boleh digunakan untuk memberikan perkhidmatan ke atas permohonan ini dan juga produk kewangan lain. Saya/Kami memahami bahawa saya/kami mempunyai hak untuk mendapatkan dan memohon pembetulan dibuat ke atas mana-mana maklumat persendirian yang disimpan oleh DearTime. Permohonan tersebut boleh dibuat di Pusat Khidmat Pelanggan DearTime.','title_ch'=>'本人/我们理解并同意，DearTime可能会将收集或持有的任何个人信息（无论是通过本应用程序还是通过其他方式获取）使用和披露给与DearTime或与之相关的个人/机构或在马来西亚境内或境外的任何选定的第三方如再保险公司，索赔调查公司和行业协会来处理该申请。该信息还可用于为该金融产品和其他金融产品提供服务，并与我/我们进行通信。本人/我们了解，本人/我们有权访问并要求更正DearTime持有的任何个人信息。此类要求可向DearTime客户服务中心提出。','type'=>'checkbox','data'=>['required'=>true]],

        ];
        $ci = [
            46 => ['title'=>'I/We understand that DearTime\'s acceptance of this form is not admission of DearTime\'s liability of my/our claim	','title_bm'=>'Saya/Kami memahami bahawa penerimaan borang oleh DearTime tidak boleh dianggap sebagai penerimaan liabiliti ke atas tuntutan yang dibuat	','title_ch'=>'本人/我们了解，DearTime接受此表格并非代表DearTime对我/我们的索赔承担责任','type'=>'checkbox','data'=>['required'=>true]],
//            47 => ['title'=>'I/We authorise any institution or individual that has any records or knowledge of my/our health and medical history to disclose such information to DearTime or its representative','title_bm'=>'Saya/Kami memberi kuasa kepada mana-mana institusi atau individu yang mempunyai rekod atau maklumat tentang kesihatan dan sejarah perubatan saya/kami untuk mendedahkannya kepada DearTime atau wakilnya.','title_ch'=>'本人/我们授权任何拥有我/我们的健康和病史记录或知识的机构或个人向DearTime或其代表披露此类信息','type'=>'checkbox','data'=>['required'=>true]],
//            48 => ['title'=>'I/We understand and agree that any personal information collected or held by DearTime (whether through this application or otherwise obtained) maybe used and disclosed by DearTime to individuals/institutions related to and associated with DearTime or any selected third party within or outside Malaysia such as reinsurers, claims investigation companies and industry associations to process the application. The information may also be used to provide service for this and other financial products and to communicate with me/us. I/We understand that I/we have a right to get access to and request for correction of any personal information held by DearTime. Such requests can be made at DearTime Customer Care.','title_bm'=>'Saya/Kami memahami dan bersetuju bahawa maklumat peribadi yang dikumpul atau dipegang oleh DearTime (sama ada melalui permohonan ini ataupun cara lain) boleh digunakan dan didedahkan kepada individu atau institusi yang berkaitan dengan DearTime atau mana-mana pihak ketiga di dalam atau di luar Malaysia seperti penanggung insurans semula (reinsurer), syarikat penyiasatan tuntutan dan persatuanindustri bagi memproses permohonan ini. Maklumat tersebut juga boleh digunakan untuk memberikan perkhidmatan ke atas permohonan ini dan juga produk kewangan lain. Saya/Kami memahami bahawa saya/kami mempunyai hak untuk mendapatkan dan memohon pembetulan dibuat ke atas mana-mana maklumat persendirian yang disimpan oleh DearTime. Permohonan tersebut boleh dibuat di Pusat Khidmat Pelanggan DearTime.','title_ch'=>'本人/我们理解并同意，DearTime可能会将收集或持有的任何个人信息（无论是通过本应用程序还是通过其他方式获取）使用和披露给与DearTime或与之相关的个人/机构或在马来西亚境内或境外的任何选定的第三方如再保险公司，索赔调查公司和行业协会来处理该申请。该信息还可用于为该金融产品和其他金融产品提供服务，并与我/我们进行通信。本人/我们了解，本人/我们有权访问并要求更正DearTime持有的任何个人信息。此类要求可向DearTime客户服务中心提出。','type'=>'checkbox','data'=>['required'=>true]],

        ];


        ClaimQuestion::query()->truncate();
        ClaimQuestionAnswer::query()->truncate();

        $data = ['1'=>$death,'2'=>$disability,'3'=>$accident,'4'=>$ci];
        foreach ($data as $n=>$d) {
            foreach ($d as $key=>$item) {
                $claim_question = new ClaimQuestion();
                $claim_question->id = $key;
                $claim_question->product_id = $n;
                $claim_question->title = $item['title'];
                $claim_question->title_bm = $item['title_bm'];
                $claim_question->title_ch = $item['title_ch'];
                $claim_question->type = $item['type'];
                $claim_question->data = json_encode($item['data'] ?? []);
                $claim_question->save();
                if(!empty($item['content'])){
                    foreach ($item['content'] as $keyi=>$i) {

                        $claim_question_answer = new ClaimQuestionAnswer();
                        $claim_question_answer->answer_id = $keyi;
                        $claim_question_answer->question_id = $claim_question->id;
                        $claim_question_answer->title = $i['title'];
                        $claim_question_answer->title_bm = $i['title_bm'];
                        $claim_question_answer->title_ch = $i['title_ch'];
                        $claim_question_answer->save();

                    }

                }
            }
        }

    }
}
