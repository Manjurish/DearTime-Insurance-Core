<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeneficiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->integer('individual_id');
            $table->integer('nominee_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('nationality');
            $table->string('nric');
            $table->string('gender');
            $table->string('passport_expiry_date')->nullable();
            $table->string('dob');
            $table->string('relationship');
            $table->string('type');
            $table->double('percentage');
            $table->string('status');
            $table->integer('has_living_spouse_child')->default(0);
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
        Schema::dropIfExists('beneficiaries');
    }
}
