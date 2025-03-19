<?php     

namespace Database\Seeders;

use App\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'key'=>'system_extra_day',
            'value'=>0
        ]);
        Config::create([
            'key'=>'system_extra_hour',
            'value'=>0
        ]);
        Config::create([
            'key'=>'user_screening',
            'value'=>'deactive'
        ]);
    }
}
