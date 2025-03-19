<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUwsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uws', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('group_id');
            $table->string('title');
            $table->string('title_bm')->nullable();
            $table->string('title_zh')->nullable();
            $table->string('info')->nullable();
            $table->string('info_bm')->nullable();
            $table->string('info_zh')->nullable();
            $table->string('gender')->default('all');
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
        Schema::dropIfExists('uws');
    }
}
