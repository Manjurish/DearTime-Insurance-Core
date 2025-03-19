<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // refund / payout REQUEST
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('action_id'); // maybe put type in action table, settlement for promoter
            //$table->string('type'); //objection, cancellation, termination
            $table->string('payer'); // charity_fund, deartime
            $table->integer('user_id'); // receiver (users)
            $table->integer('bank_account_id'); // refer to bank account table // todo add remove
            $table->float('amount');
            $table->integer('authorized_by')->nullable(); // internal user id
            $table->dateTime('authorized_at')->nullable(); // internal user id when authorized
            $table->dateTime('effective_date')->nullable(); // when to issue refund
            $table->string('status'); // pending, approve | reject, completed
            $table->string('pay_ref_no')->nullable();
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
        Schema::dropIfExists('refunds');
    }
}
