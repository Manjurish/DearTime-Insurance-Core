<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndustryJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('industry_id');
            $table->string('name');
            $table->string('name_bm');
            $table->string('name_ch');
//            $table->string('code');
            $table->string('gender')->nullable();
            $table->double('death')->default(0);
            $table->double('Accident')->default(1);
            $table->double('TPD')->default(1);
            $table->double('Medical')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('industry_jobs');
    }
}
