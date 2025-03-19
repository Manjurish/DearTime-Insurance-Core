<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // FOR TESTING ONLY, WILL NEED TO FOLLOW PCI DSS COMPLIANCE
        Schema::create('bank_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('owner_id'); // for both individual & group
            $table->string('owner_type');
            $table->string('token');
            $table->string('bank_code')->nullable();
            $table->dateTime('saved_date')->nullable();
            $table->string('scheme')->nullable();
            $table->string('masked_pan')->nullable();
            $table->string('holder_name')->nullable();
            $table->smallInteger('expiry_month')->nullable();
            $table->smallInteger('expiry_year')->nullable();
            $table->string('code')->nullable();
            $table->string('message')->nullable();
            $table->dateTime('last_checked')->nullable();
//            $table->string('cc_num');
//            $table->string('cc_type');
//            $table->string('cc_msg');
//            $table->boolean('status')->default(false);
//            $table->dateTime('last_checked')->nullable();
            $table->boolean('auto_debit')->default(1);
//            $table->string('cc');
//            $table->smallInteger('cvv');
//            $table->string('expiry');
//            $table->string('bank')->default('VISA');
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
        Schema::dropIfExists('bank_cards');
    }
}
