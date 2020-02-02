<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LimitResourceToOneContentEach extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resource_contents', function($table)
        {
            $table->unique('resource_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resource_contents', function($table)
        {
            // if we want to drop a unique constraint on a column, we must first drop any foreign keys
            $table->dropForeign('resource_contents_resource_id_foreign'); // this code might not work in laravel 5.7+
            // now we can actually drop the unique constraint
            $table->dropUnique('resource_contents_resource_id_unique');
            // and now we can add the foreign key back
            $table->foreign('resource_id')->references('id')->on('resources');
        });
    }
}
