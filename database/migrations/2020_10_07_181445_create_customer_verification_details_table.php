<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerVerificationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_verification_details', function (Blueprint $table) {
            $table->id();
            $table->integer     ('kyc_id');
            $table->string      ('status')              ->default('Pending');
            $table->text        ('note')                ->nullable();
            $table->text        ('description')         ->nullable();
            $table->string      ('type'); // staff || user
            $table->integer     ('created_by')           ->nullable();
            $table->timestamps  ();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_verification_details');
    }
}
