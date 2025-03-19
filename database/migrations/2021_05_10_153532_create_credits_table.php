<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credits', function (Blueprint $table) {
            // self, promoter, charity
            // promoter
            $table->id();
            $table->uuid('uuid');
            $table->uuid('ref_no');
            $table->integer('order_id')->nullable(); // if thanksgiving and self
            $table->integer('user_id')->nullable(); // user id, if null then charity fund
            $table->integer('from_id')->nullable(); // sender user id, if null deartime
            $table->float('amount');
            $table->string('type'); //thanksgiving, GP fee reimbursement, refund, (promoter_credit, promoter_settlement) always negative
            $table->integer('type_item_id'); // thanksgiving item id, to find the percentage
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
        Schema::dropIfExists('credits');
    }
}
