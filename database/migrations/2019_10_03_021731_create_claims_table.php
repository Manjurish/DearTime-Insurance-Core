<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
            $table->uuid('uuid')->unique();
            $table->integer('individual_id');
            $table->uuid('coverage_id'); // purchased coverage id
            $table->integer('owner_id')->nullable();
            $table->string('status')->default('draft');
            $table->integer('created_by'); // user id
            $table->integer('panel_id');
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
        Schema::dropIfExists('claims');
    }
}
