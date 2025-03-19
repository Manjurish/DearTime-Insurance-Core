<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJuvenileBmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('juvenile_bmis', function (Blueprint $table) {
            $table->id();
            $table->integer('age');
            $table->string('gender');
            $table->integer('height_min');
            $table->integer('height_max');
            $table->float('weight_min');
            $table->float('weight_max');
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
        Schema::dropIfExists('juvenile_bmis');
    }
}
