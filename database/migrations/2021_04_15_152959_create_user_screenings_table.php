<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserScreeningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_screenings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default(null); // for search_term
            $table->unsignedBigInteger('user_id'); // user id in users table
            $table->string('ref'); // search query reference
            $table->string('match_status'); // for match_status
            $table->integer('total_hits'); // number of match
            $table->string('risk_level'); // risk_level
            $table->string('fuzziness'); // fuzziness
            $table->bigInteger('assignee_id'); // assignee_id
            $table->string('status')->default('approve')->comment('approve, reject, pending');
            $table->longText('details')->default(null);
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
        Schema::dropIfExists('user_screenings');
    }
}
