<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndividualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individuals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('user_id');
            $table->string('name');
            $table->string('nric')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->string('country_id')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile')->nullable();
            $table->float('household_income')->nullable();
            $table->float('personal_income')->nullable();
            $table->integer('occ')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->integer('address_id')->nullable();
            $table->boolean('has_other_life_insurance')->default(false);
            $table->boolean('in_restricted_age')->default(false);
            $table->boolean('is_restricted_foreign')->default(false);
            $table->string('type')->default('owner');
            $table->integer('owner_id')->default(0);
            $table->string('fund_source')->nullable();

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
        Schema::dropIfExists('individuals');
    }
}
