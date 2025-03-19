<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoveragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coverages', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
            $table->uuid('uuid');
            $table->integer('owner_id');
            $table->integer('payer_id');
            $table->integer('covered_id');
            $table->integer('product_id');
            $table->string('product_name');
//            $table->integer('promoter_id')->nullable();
            $table->string('status')->default('draft'); // active, suspended, grace, pending
            $table->string('state')->default('inactive'); // active , inactive
            $table->string('payment_term'); // monthly / yearly
            $table->bigInteger('coverage');
            $table->bigInteger('deductible')->nullable();
            $table->bigInteger('max_coverage');
            $table->float('payment_monthly');
            $table->float('payment_annually');
            $table->boolean('has_loading')->default(false);
//            $table->json('underwriting')->nullable();
            $table->integer('uw_id')->nullable();
            $table->dateTime('first_payment_on')->nullable();
            $table->dateTime('next_payment_on')->nullable();
            $table->dateTime('last_payment_on')->nullable();
            $table->dateTime('started_on')->nullable();
            $table->dateTime('ended_on')->nullable(); //deactivate_on (when remove card or payment fail)
            $table->string('color')->nullable();
            $table->integer('is_accepted_by_owner')->default(0);
            $table->integer('parent_id')->default(null);
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
        Schema::dropIfExists('coverages');
    }
}
