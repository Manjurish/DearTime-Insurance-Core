<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->integer('documentable_id');
            $table->string('documentable_type');
            $table->string('type')->nullable();
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->string('name');
            $table->string('ext');
            $table->string('url');
            $table->bigInteger('size');
            $table->integer('created_by')->nullable();
            $table->uuid('created_by_uuid')->nullable(); // TODO remove integer created_by and use UUID only
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
        Schema::dropIfExists('documents');
    }
}
