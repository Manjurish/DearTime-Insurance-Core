<?php

namespace Database\Seeders;
use App\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder3 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'key'=>'ekyc_strict_comparision',
            'value'=>'active'
        ]);
        Config::create([
            'key'=>'invalid_login_attempts',
            'value'=>'3'
        ]);
    }
}
