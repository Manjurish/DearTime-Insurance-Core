<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnderwritingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('underwritings', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
            $table->uuid('uuid');
            $table->integer('individual_id');
            $table->text('answers');
            $table->boolean('death')->default(false);
            $table->boolean('disability')->default(false);
            $table->boolean('ci')->default(false);
            $table->boolean('medical')->default(false);
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('underwritings');
    }
}
