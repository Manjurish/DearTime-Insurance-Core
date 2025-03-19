<?php

namespace Database\Seeders;
use App\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'key'=>'text_comparision',
            'value'=>'deactive'
        ]);
        Config::create([
            'key'=>'face_comparision',
            'value'=>'deactive'
        ]);
        Config::create([
            'key'=>'default_face_compare_result',
            'value'=>'fail'
        ]);
    }
}
