<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPAClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_p_a_claims', function (Blueprint $table) {
            $table->id();
			$table->uuid('uuid');
			$table->char('claim_type',1);
            $table->string('claim_no',10)->unique();
            $table->string('policy_no',50);
            $table->string('id_no',50);
            $table->date('date_of_visit');
            $table->date('date_of_discharge');
            $table->string('diagnosis_code_1',10);
            $table->string('diagnosis_code_2',10);
            $table->string('diagnosis_code_3',10);
            $table->string('provider_code',4);
            $table->string('provider_name',200);
            $table->string('provider_invoice_no',50);
            $table->date('date_claim_received');
            $table->date('medical_leave_from');
            $table->date('medical_leave_to');
            $table->string('tpa_invoice_no',10);
            $table->char('cliam_type',1);
            $table->float('actual_invoice_amount');
            $table->float('approved_amount');
            $table->float('non_approved_amount');
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
        Schema::dropIfExists('t_p_a_claims');
    }
}
