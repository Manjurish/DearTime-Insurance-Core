<?php     
namespace Database\Seeders;

use App\juvenileBmi;
use App\Uw;
use App\UwGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;


class JuvenileBmiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    var $data = [
        ['age'=>'0','f_min_h'=>'46','f_max_h'=>'53','f_min_w'=>'2.4','f_max_w'=>'4.2','m_min_h'=>'46','m_max_h'=>'54','m_min_w'=>'2.5','m_max_w'=>'4.5'],
        ['age'=>'1','f_min_h'=>'50','f_max_h'=>'58','f_min_w'=>'3.1','f_max_w'=>'5.5','m_min_h'=>'50','m_max_h'=>'59','m_min_w'=>'3.5','m_max_w'=>'6.0'],
        ['age'=>'2','f_min_h'=>'53','f_max_h'=>'61','f_min_w'=>'4.0','f_max_w'=>'6.4','m_min_h'=>'54','m_max_h'=>'63','m_min_w'=>'4.3','m_max_w'=>'7.0'],
        ['age'=>'3','f_min_h'=>'55','f_max_h'=>'65','f_min_w'=>'4.5','f_max_w'=>'7.5','m_min_h'=>'57','m_max_h'=>'66','m_min_w'=>'5.0','m_max_w'=>'8.0'],
        ['age'=>'4','f_min_h'=>'58','f_max_h'=>'66','f_min_w'=>'5.0','f_max_w'=>'8.2','m_min_h'=>'60','m_max_h'=>'68','m_min_w'=>'5.5','m_max_w'=>'8.8'],
        ['age'=>'5','f_min_h'=>'59','f_max_h'=>'68','f_min_w'=>'5.4','f_max_w'=>'8.9','m_min_h'=>'62','m_max_h'=>'70','m_min_w'=>'6.0','m_max_w'=>'9.4'],
        ['age'=>'6','f_min_h'=>'61','f_max_h'=>'70','f_min_w'=>'5.8','f_max_w'=>'9.4','m_min_h'=>'63','m_max_h'=>'72','m_min_w'=>'6.4','m_max_w'=>'9.8'],
        ['age'=>'7','f_min_h'=>'63','f_max_h'=>'72','f_min_w'=>'6.0','f_max_w'=>'9.8','m_min_h'=>'65','m_max_h'=>'74','m_min_w'=>'6.8','m_max_w'=>'10.2'],
        ['age'=>'8','f_min_h'=>'64','f_max_h'=>'74','f_min_w'=>'6.2','f_max_w'=>'10.2','m_min_h'=>'66','m_max_h'=>'75','m_min_w'=>'7.0','m_max_w'=>'10.8'],
        ['age'=>'9','f_min_h'=>'65','f_max_h'=>'75','f_min_w'=>'6.5','f_max_w'=>'10.5','m_min_h'=>'67','m_max_h'=>'76','m_min_w'=>'7.1','m_max_w'=>'11.0'],
        ['age'=>'10','f_min_h'=>'66','f_max_h'=>'76','f_min_w'=>'6.8','f_max_w'=>'11.0','m_min_h'=>'69','m_max_h'=>'78','m_min_w'=>'7.4','m_max_w'=>'11.4'],
        ['age'=>'11','f_min_h'=>'68','f_max_h'=>'78','f_min_w'=>'6.9','f_max_w'=>'11.2','m_min_h'=>'70','m_max_h'=>'79','m_min_w'=>'7.6','m_max_w'=>'11.8'],
        ['age'=>'12','f_min_h'=>'69','f_max_h'=>'79','f_min_w'=>'7.0','f_max_w'=>'11.6','m_min_h'=>'71','m_max_h'=>'81','m_min_w'=>'7.8','m_max_w'=>'12.0'],
        ['age'=>'13','f_min_h'=>'70','f_max_h'=>'81','f_min_w'=>'7.2','f_max_w'=>'11.9','m_min_h'=>'72','m_max_h'=>'82','m_min_w'=>'8.0','m_max_w'=>'12.2'],
        ['age'=>'14','f_min_h'=>'71','f_max_h'=>'82','f_min_w'=>'7.4','f_max_w'=>'12.0','m_min_h'=>'73','m_max_h'=>'83','m_min_w'=>'8.1','m_max_w'=>'12.5'],
        ['age'=>'15','f_min_h'=>'72','f_max_h'=>'83','f_min_w'=>'7.6','f_max_w'=>'12.4','m_min_h'=>'74','m_max_h'=>'84','m_min_w'=>'8.2','m_max_w'=>'12.9'],
        ['age'=>'16','f_min_h'=>'73','f_max_h'=>'84','f_min_w'=>'7.8','f_max_w'=>'12.8','m_min_h'=>'75','m_max_h'=>'85','m_min_w'=>'8.4','m_max_w'=>'13.1'],
        ['age'=>'17','f_min_h'=>'74','f_max_h'=>'85','f_min_w'=>'8.0','f_max_w'=>'13.0','m_min_h'=>'76','m_max_h'=>'87','m_min_w'=>'8.6','m_max_w'=>'13.4'],
        ['age'=>'18','f_min_h'=>'75','f_max_h'=>'87','f_min_w'=>'8.1','f_max_w'=>'13.2','m_min_h'=>'77','m_max_h'=>'88','m_min_w'=>'8.8','m_max_w'=>'13.8'],
        ['age'=>'19','f_min_h'=>'76','f_max_h'=>'88','f_min_w'=>'8.2','f_max_w'=>'13.5','m_min_h'=>'78','m_max_h'=>'89','m_min_w'=>'9.0','m_max_w'=>'14.0'],
        ['age'=>'20','f_min_h'=>'77','f_max_h'=>'89','f_min_w'=>'8.4','f_max_w'=>'13.8','m_min_h'=>'78','m_max_h'=>'90','m_min_w'=>'9.1','m_max_w'=>'14.2'],
        ['age'=>'21','f_min_h'=>'77','f_max_h'=>'90','f_min_w'=>'8.6','f_max_w'=>'14.0','m_min_h'=>'79','m_max_h'=>'91','m_min_w'=>'9.2','m_max_w'=>'14.5'],
        ['age'=>'22','f_min_h'=>'78','f_max_h'=>'91','f_min_w'=>'8.8','f_max_w'=>'14.2','m_min_h'=>'80','m_max_h'=>'92','m_min_w'=>'9.4','m_max_w'=>'14.8'],
        ['age'=>'23','f_min_h'=>'79','f_max_h'=>'92','f_min_w'=>'8.9','f_max_w'=>'14.6','m_min_h'=>'81','m_max_h'=>'93','m_min_w'=>'9.6','m_max_w'=>'15.0'],
        ['age'=>'24','f_min_h'=>'80','f_max_h'=>'93','f_min_w'=>'9.0','f_max_w'=>'15.0','m_min_h'=>'82','m_max_h'=>'94','m_min_w'=>'9.8','m_max_w'=>'15.2'],
    ];
    public function run()
    {
        foreach ($this->data as $dt) {
            $jb = new juvenileBmi();
            $jb->age = $dt['age'];
            $jb->gender = 'male';
            $jb->height_min =  $dt['m_min_h'];
            $jb->height_max =  $dt['m_max_h'];
            $jb->weight_min =  $dt['m_min_w'];
            $jb->weight_max =  $dt['m_max_w'];
            $jb->save();

            $jb = new juvenileBmi();
            $jb->age = $dt['age'];
            $jb->gender = 'female';
            $jb->height_min =  $dt['f_min_h'];
            $jb->height_max =  $dt['f_max_h'];
            $jb->weight_min =  $dt['f_min_w'];
            $jb->weight_max =  $dt['f_max_w'];
            $jb->save();

        }
    }
}
