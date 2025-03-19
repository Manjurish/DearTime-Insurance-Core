<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelfieMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selfie_matches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('individual_id');
            $table->float('similarity');
            $table->string('face_id')->nullable();
            $table->string('image_id')->nullable();
            $table->string('collection')->nullable();
            $table->json('data')->nullable();
            $table->json('face')->nullable();
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
        Schema::dropIfExists('selfie_matches');
    }
}
