<?php     

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowAndExecuteOnToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
			$table->dateTime('execute_on')->nullable()->after('auto_read');
			$table->boolean('show')->default(1)->after('auto_read');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
			$table->dropIfExists('execute_on');
			$table->dropIfExists('show');
		});
    }
}
