<?php     

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPackageMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_package_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->integer('package_id');
            $table->integer('individual_id')->nullable(); // if null means user not registered
            $table->string('name');
            $table->string('email');
            $table->string('nationality');
            $table->string('nric');
            $table->string('gender');
            $table->date('dob');
            $table->date('passport_expiry_date')->nullable();
            $table->string('mobile');
            $table->string('status');
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
        Schema::dropIfExists('group_package_members');
    }
}
