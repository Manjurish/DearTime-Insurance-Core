<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',20)->nullable();
            $table->uuid('uuid');
            $table->string('type');
//            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('marketing_email')->default(true);
            $table->string('password')->nullable();
            $table->string('activation_token')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('locale')->default('en');
            $table->integer('identity_verified_by')->nullable();
            $table->dateTime('identity_verified_on')->nullable();
            $table->integer('promoter_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
