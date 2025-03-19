<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('company_id');
            $table->string('name');
            $table->float('DTH')->nullable();
            $table->float('ADD')->nullable();
            $table->float('TPD')->nullable();
            $table->boolean('MC1')->nullable();
            $table->float('CI')->nullable();
            $table->string('status'); // active, suspended
            $table->string('payment_term'); // monthly, annually
            $table->float('payment_monthly');
            $table->float('payment_annually');
            $table->dateTime('next_payment_on')->nullable();
            $table->dateTime('last_payment_on')->nullable();
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
        Schema::dropIfExists('group_packages');
    }
}
