<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
//            $table->integer('underwriting_id'); // we need to keep a copy of underwriting for each order
            $table->uuid('uuid');
            $table->float('amount');
            $table->float('true_amount');
            $table->string('status')->default('unpaid'); // rejected, paid, cancelled, replaced
//            $table->string('transaction_id');
            $table->integer('payer_id');
//            $table->string('msg');
//            $table->string('card_no');
            $table->string('retries')->default(5);
//            $table->string('card_scheme');
            $table->string('type')->default('new'); // new, renew
            $table->integer('grace_period')->default(30); // 30 or 90
            $table->dateTime('next_try_on');
            $table->dateTime('last_try_on');
            $table->dateTime('due_date');
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
        Schema::dropIfExists('orders');
    }
}
