<?php     
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminSeeder::class);
        $this->call(UwSeeder::class);
        $this->call(JuvenileBmiSeeder::class);
        $this->call(IndustryJobsSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(ConfigurationSeeder::class);
        $this->call(ClaimQuestionSeeder::class);
        $this->call(StateSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(HCClinicSeeder::class);
        $this->call(ForeignQuestionSeeder::class);
        $this->call(ConfigSeeder::class);
        Artisan::call('update-docx');
    }
}
