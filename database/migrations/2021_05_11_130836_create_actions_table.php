<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('ref_no',20)->nullable();
            $table->unsignedBigInteger('user_id'); // users
            $table->integer('parent_id')->nullable(); // if this action is sub-action. eg: free-look-cancellation triggered cancellation
            $table->string('type');    // particular-change, amendment (everything related to coverage)
            $table->string('event');   // enum: [change-dob, change-gender, change-thanksgiving, change-beneficiary], free-look-cancellation, switch-payment-mode (monthly <> annually), new-purchase-increase, new-purchase
            // new table for coverages_actions
            //   action_id         coverage_id
            $table->integer('previous_action_id')->nullable(); // find the order to execute actions
            $table->json('actions'); // parameters:

            // some actions need approval before execution (internal user triggered)
           // $table->boolean('needs_approval')->default(false);
            $table->string('status'); // [pending-payment, pending-next-due-date], pending-review, executed
            $table->dateTime('execute_on')->nullable();
            $table->dateTime('due_date')->nullable();
            // approved and executed the action
            $table->dateTime('approved_on')->nullable(); // if null = pending
            $table->integer('approved_by')->nullable(); // internal user

            // created the action
            $table->morphs('createdbyable');

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
        Schema::dropIfExists('actions');
    }
}
