<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceClassColumn extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('resources', function (Blueprint $table) {
			$table->integer('class_id')->unsigned()->nullable();
			$table->foreign('class_id')->references('id')->on('classes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('resources', function (Blueprint $table) {
			$table->dropForeign('resources_class_id_foreign'); // this code might not work in laravel 5.7+
			$table->dropColumn('class_id');
		});
	}
}
