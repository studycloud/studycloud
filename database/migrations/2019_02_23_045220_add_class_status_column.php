<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClassStatusColumn extends Migration
{
	/**
	 * Run the migrations.
	 * The status column will indicate whether this class can be attached to resources. It defaults to allowed
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('classes', function (Blueprint $table) {
			$table->integer('status')->default(1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$table->dropColumn('status');
	}
}
