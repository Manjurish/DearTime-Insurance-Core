<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharityApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charity_applicants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('individual_id');
            $table->string('about_self')->nullable();
            $table->string('sponsor_thank_note')->nullable();
            $table->integer('dependants')->default(0);
            $table->boolean('active')->default(false);
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
        Schema::dropIfExists('charity_applicants');
    }
}
