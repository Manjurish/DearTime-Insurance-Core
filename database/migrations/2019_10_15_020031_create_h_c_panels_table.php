<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHCPanelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h_c_panels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('type');
            $table->string('code')->unique();
            $table->string('reg_no');
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('post_code');
            $table->string('phone');
            $table->string('fax');
            $table->json('email');
            $table->string('contact_name');
            $table->string('doctor_name');
            $table->string('bank_account');
            $table->string('billing_name');
            $table->string('billing_address');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
//            $table->
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h_c_panels');
    }
}
