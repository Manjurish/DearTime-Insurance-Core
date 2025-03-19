<?php     

namespace Database\Seeders;

use App\Uw;
use App\UwGroup;
use Illuminate\Database\Seeder;


class UwSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	var $questions = [
		'health'    => [
			'title'     => 'Have you been medically advised, treated or diagnosed with (select at least one):',
			'title_bm'  => 'Adakah anda pernah menerima nasihat perubatan, dirawat atau didiagnos dengan (pilih sekurang-kurangnya satu):',
			'title_zh'  => '你有没有被医生建议，治疗或诊断为（选择至少一个）：',
			'questions' => [
				['id'      => 0,'title' => 'Heart Disease','title_bm' => 'Penyakit Jantung','title_zh' => '心脏病',
				 'info'    => 'heart attack, chest pain, heart valve problem, etc.',
				 'info_bm' => 'serangan jantung, sakit dada, masalah injap jantung, dan lain lain',
				 'info_zh' => '心脏发作，胸痛，心脏瓣膜问题，等',
				 'value'   => NULL,
				 'gender'  => 'all'
				],
				['id' => 1,'title' => 'Hypertension','title_bm' => 'Hipertensi','title_zh' => '高血压','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 2,'title' => 'High Blood Pressure','title_bm' => 'Tekanan darah tinggi','title_zh' => '','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 3,'title' => 'High Cholesterol','title_bm' => 'Kolestrol tinggi','title_zh' => '高胆固醇','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 4,'title' => 'Diabetes','title_bm' => 'Kencing Manis','title_zh' => '糖尿病','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 5,'title' => 'Sugar In Urine','title_bm' => 'gula dalam air kencing','title_zh' => '糖尿','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 6,'title' => 'High Blood Sugar','title_bm' => 'tinggi gula dalam darah','title_zh' => '高血糖','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 7,'title' => 'Cancer','title_bm' => 'Kanser','title_zh' => '癌症','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 8,'title' => 'Tumour','title_bm' => 'tumor','title_zh' => '瘤','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 9,'title' => 'Lump','title_bm' => 'benjolan','title_zh' => '块状','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 10,'title' => 'Cyst','title_bm' => 'sista','title_zh' => '囊肿','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 11,'title' => 'Abnormal Growth','title_bm' => 'pertumbuhan yang tidak normal','title_zh' => '异常生长','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id'      => 12,'title' => 'Liver Disease','title_bm' => 'Penyakit hati','title_zh' => '肝病',
				 'info'    => 'Hepatitis B, Hepatitis C, Cirrhosis etc.',
				 'info_bm' => 'Hepatitis B, Hepatitis C, Cirrhosis dan lain lain',
				 'info_zh' => '乙型肝炎，丙型肝炎，肝硬化， 等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 13,'title' => 'Mental Health Problems','title_bm' => 'Masalah kesihatan mental','title_zh' => '精神健康问题',
				 'info'    => 'depression, anxiety, schizophrenia etc.',
				 'info_bm' => 'kemurungan, sering bimbang, skizofrenia dan lain-lain',
				 'info_zh' => '抑郁，焦虑，精神分裂，等',
				 'value'   => NULL,'gender' => 'all'],
				['id' => 14,'title' => 'AIDS','title_bm' => 'AIDS','title_zh' => '爱滋病','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 15,'title' => 'HIV','title_bm' => 'HIV','title_zh' => 'HIV','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 16,'title' => 'Alcoholism','title_bm' => 'Ketagihan alkohol','title_zh' => '酗酒','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 17,'title' => 'Drug Abuse','title_bm' => 'Penyalah gunaan dadah','title_zh' => '吸毒','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 18,'title' => 'Physical Defect','title_bm' => 'Kecacatan fizikal','title_zh' => '生理缺陷','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 19,'title' => 'Congenital Abnormality','title_bm' => 'Abnormaliti kongenital','title_zh' => '先天畸形','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 34,'title' => 'None','title_bm' => 'Tiada','title_zh' => '没有','info' => NULL,'value' => NULL,'gender' => 'all'],
			]
		],
		'health2'   => [
			'title'     => 'Have you ever had disorders of (select at least one):',
			'title_bm'  => 'Adakah anda pernah mengalami gangguan (pilih sekurang-kurangnya satu):',
			'title_zh'  => '您是否曾经患有以下疾病（选择至少一个）：',
			'questions' => [
				['id'      => 20,'title' => 'Blood','title_bm' => 'Darah','title_zh' => '血液',
				 'info'    => 'anaemia, thalassemia major, hemophilia etc.',
				 'info_bm' => 'anemia, thalassemia yang teruk, hemofilia dan lain lain',
				 'info_zh' => '贫血，重度地中海贫血，血友病等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 21,'title' => 'Breathing/Lung','title_bm' => 'Bernafas / Paru-paru','title_zh' => '呼吸/肺疾',
				 'info'    => 'asthma, tuberculosis, emphysema etc.',
				 'info_bm' => 'asma, batuk kering, emfisema dan lain lain',
				 'info_zh' => '哮喘，肺结核，气肿等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 22,'title' => 'Kidney/Urinary','title_bm' => 'Buah pinggang / kencing','title_zh' => '肾/泌尿',
				 'info'    => 'kidney failure, kidney stone, nephritis, etc.',
				 'info_bm' => 'kegagalan buah pinggang, batu dalam buah pinggang, nefritis dan lain lain',
				 'info_zh' => '肾功能衰竭，肾结石，肾炎等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 23,'title' => 'Digestive System','title_bm' => 'Sistem penghadaman','title_zh' => '消化系统',
				 'info'    => 'including stomach, bowel, gall bladder, pancreas, gastric ulcer, small intestine, etc.',
				 'info_bm' => 'termasuk perut, usus, pundi hempedu, pankreas, ulser gastrik, usus kecil dan lain lain',
				 'info_zh' => '包括胃，肠，胆囊，胰腺，胃溃疡，小肠等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 24,'title' => 'Neurological','title_bm' => 'Neurologi','title_zh' => '神经系统',
				 'info'    => 'epilepsy, paralysis, Multiple Sclerosis, etc.',
				 'info_bm' => 'epilepsi, lumpuh, Sklerosis Berbilang dan lain lain',
				 'info_zh' => '癫痫，瘫痪，多发性硬化等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 25,'title' => 'Musculoskeletal/Joint','title_bm' => 'Musculoskeletal / sendi','title_zh' => '肌肉骨骼/关节',
				 'info'    => 'osteoporosis, arthritis, gout, etc.',
				 'info_bm' => 'osteoporosis, arthritis, gout, dan lain lain',
				 'info_zh' => '骨质疏松症，关节炎，痛风等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 26,'title' => 'Brain/Nerve','title_bm' => 'Otak / saraf','title_zh' => '脑/神经障碍',
				 'info'    => 'stroke, Alzheimer, Parkinson etc.',
				 'info_bm' => 'strok, Alzheimer, Parkinson dan lain lain',
				 'info_zh' => '中风，老年痴呆，帕金森等',
				 'value'   => NULL,'gender' => 'all'],
				['id' => 27,'title' => 'Thyroid','title_bm' => 'Tiroid','title_zh' => '甲状腺','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 28,'title' => 'Prostate','title_bm' => 'prostat','title_zh' => '前列腺','info' => NULL,'value' => NULL,'gender' => 'male'],
				['id' => 29,'title' => 'Ears','title_bm' => 'Mata','title_zh' => '眼睛','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 30,'title' => 'Eyes','title_bm' => 'Telinga','title_zh' => '耳','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 31,'title' => 'Nose','title_bm' => 'Hidung','title_zh' => '鼻','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 32,'title' => 'Throat','title_bm' => 'Tekak','title_zh' => '咽喉','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 33,'title' => 'Hereditary','title_bm' => 'Keturunan','title_zh' => '遗传性','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id'      => 35,'title' => 'Female Organ','title_bm' => 'Organ wanita','title_zh' => '女性器官',
				 'info'    => 'breast, ovary, uterus, etc.',
				 'info_bm' => 'payudara, ovari, rahim, dan lain-lain',
				 'info_zh' => '乳腺，卵巢，子宫等',
				 'value'   => NULL,'gender' => 'female'],
				['id' => 53,'title' => 'None','title_bm' => 'Tiada','title_zh' => '没有','info' => NULL,'value' => NULL,'gender' => 'all'],
			]
		],
		'family'    => [
			'title'     => 'Do you have at least 2 parents/siblings by age 50 with (select at least one):',
			'title_bm'  => 'Adakah anda mempunyai sekurang-kurangnya 2 ahli keluarga samada ibu bapa / adik beradik berusia 50 tahun mengalami (pilih sekurang-kurangnya satu):',
			'title_zh'  => '你有至少2个父母或兄弟姐妹在50岁或之前患上（选择至少一个）：',
			'questions' => [
				['id' => 36,'title' => 'Heart Disease','title_bm' => 'Penyakit Jantung','title_zh' => '心脏病','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 37,'title' => 'Kidney Disease','title_bm' => 'Penyakit Buah Pinggang','title_zh' => '肾脏疾病','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 38,'title' => 'Stroke','title_bm' => 'Strok','title_zh' => '中风','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 40,'title' => 'Diabetes','title_bm' => 'Kencing manis','title_zh' => '糖尿病','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 55,'title' => 'Cancer','title_bm' => 'Kanser','title_zh' => '癌症','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 39,'title' => 'None','title_bm' => 'Tiada','title_zh' => '没有','info' => NULL,'value' => NULL,'gender' => 'all'],
			]],
		'lifestyle' => [
			'title'     => 'Do you participate in (select at least one):',
			'title_bm'  => 'Adakah anda mengambil bahagian dalam (pilih sekurang-kurangnya satu):',
			'title_zh'  => '你有参于（至少选择至少一个）',
			'questions' => [
				['id' => 54,'title' => 'Competitive Boxing','title_bm' => 'Bertinju secara kompetitif','title_zh' => '拳击竞赛','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id'      => 41,'title' => 'Competitive Racing','title_bm' => 'Perlumbaan secara kompetitif ','title_zh' => '竞技赛车速度 ',
				 'info'    => 'car, bike, powerboat, etc.',
				 'info_bm' => 'kereta, basikal, powerboat, dan lain lain',
				 'info_zh' => '汽车，自行车，摩托艇等',
				 'value'   => NULL,'gender' => 'all'],
				['id'      => 42,'title' => 'Self Launch Flying','title_bm' => 'Pelancaran diri sendiri untuk terbang','title_zh' => '自我发射飞行',
				 'info'    => 'Hang gliding, etc.',
				 'info_bm' => 'Meluncur gantung dan lain lain',
				 'info_zh' => '悬挂式滑翔等',
				 'value'   => NULL,'gender' => 'all'],
				['id' => 43,'title' => 'Hunting','title_bm' => 'Memburu','title_zh' => '狩猎','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 44,'title' => 'Mountaineering','title_bm' => 'Mendaki gunung','title_zh' => '登山','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 45,'title' => 'Outdoor Rock Climbing','title_bm' => 'Pendakian batu luaran','title_zh' => '户外攀岩','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 46,'title' => 'Abseiling','title_bm' => 'Abseiling','title_zh' => '吊索','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 47,'title' => 'Caving','title_bm' => 'Meneroka gua','title_zh' => '洞穴探险运动','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id'      => 48,'title' => 'Private Flying','title_bm' => 'Penerbangan peribadi','title_zh' => '私人飞行',
				 'info'    => 'fixed wing, helicopter, etc.',
				 'info_bm' => 'kenderaan sayap tetap, helikopter dan lain lain',
				 'info_zh' => '固定机翼，直升机等',
				 'value'   => NULL,'gender' => 'all'],
				['id' => 49,'title' => 'Scuba Diving >30m','title_bm' => 'Selam skuba > 30 meter','title_zh' => '潜水 > 30米','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 50,'title' => 'Skydiving/Parachuting','title_bm' => 'Terjun Udara/ payung terjun','title_zh' => '高空跳伞/跳伞','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 51,'title' => 'White Water Rafting ≥ Grade 4','title_bm' => 'Berakit di jeram ≥ Gred 4','title_zh' => '激浪泛舟 ≥ 4级','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 52,'title' => 'None','title_bm' => 'Tiada','title_zh' => '没有','info' => NULL,'value' => NULL,'gender' => 'all'],

			]
		],
		'new1'      => [
			'title'     => 'Have you been rejected or charged with loading/exclusion for your other insurances?',
			'title_bm'  => 'Pernahkah insurans anda yang lain ditolak atau dikenakan caj lebih atau pengecualian?',
			'title_zh'  => '您的其他保险是否被拒绝或需增添保费或不受保事项?',
			'questions' => [
				['id' => 56,'title' => 'Yes','title_bm' => 'Ya','title_zh' => '是','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 57,'title' => 'No','title_bm' => 'Tiada','title_zh' => '否','info' => NULL,'value' => NULL,'gender' => 'all'],

			]
		],
		'new2'      => [
			'title'     => 'Any pending investigation or surgery to be done and have you been hospitalized?',
			'title_bm'  => 'Apa-apa siasatan atau pembedahan yang belum selesai yang perlu dilakukan dan adakah anda pernah dimasukkan ke hospital?',
			'title_zh'  => '您是否有待进行的任何调查或手术和是否曾经住院？',
			'questions' => [
				['id' => 58,'title' => 'Yes','title_bm' => 'Ya','title_zh' => '是','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 59,'title' => 'No','title_bm' => 'Tiada','title_zh' => '否','info' => NULL,'value' => NULL,'gender' => 'all'],
			]
		],
		'new3'      => [
			'title'     => 'Was the child born prematured (pre-term before 37 weeks)?',
			'title_bm'  => 'Adakah anak dilahirkan pra matang (pra-tempoh sebelum 37 minggu)?',
			'title_zh'  => ' 这个孩子早产了吗（早于37周)?',
			'questions' => [
				['id' => 60,'title' => 'Yes','title_bm' => 'Ya','title_zh' => '是','info' => NULL,'value' => NULL,'gender' => 'all'],
				['id' => 61,'title' => 'No','title_bm' => 'Tiada','title_zh' => '否','info' => NULL,'value' => NULL,'gender' => 'all'],
			]
		],
	];

	public function run()
	{
		foreach ($this->questions as $name => $question) {
			$uw_group           = new UwGroup();
			$uw_group->name     = $name;
			$uw_group->title    = $question['title'];
			$uw_group->title_bm = $question['title_bm'];
			$uw_group->title_zh = $question['title_zh'];
			$uw_group->save();


			foreach ($question['questions'] as $q) {
				$uw           = new Uw();
				$uw->group_id = $uw_group->id;
				$uw->id       = $q['id'];
				$uw->title    = $q['title'];
				$uw->title_bm = $q['title_bm'];
				$uw->title_zh = $q['title_zh'];
				$uw->info     = $q['info'];
				$uw->info_bm  = $q['info_bm'] ?? NULL;
				$uw->info_zh  = $q['info_zh'] ?? NULL;
				$uw->gender   = strtolower($q['gender']);
				$uw->save();
			}
		}
	}
}
