<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
            $table->uuid('uuid');
            $table->boolean('success')->default(false);;
            $table->integer('order_id');
            $table->string('gateway', 100);
            $table->string('transaction_ref');
            $table->string('transaction_id')->nullable();
            $table->float('amount');
            $table->dateTime('date')->nullable();
            $table->string('card_type', 100)->nullable();
            $table->string('card_no', 100)->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->boolean('is_local_bin')->default(false);
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
        Schema::dropIfExists('transactions');
    }
}
