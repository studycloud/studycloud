<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUserJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userid');
            $table->integer('jobid');
            $table->timestamps();
            // sets a unique constraint on the userid/jobid combo
            $table->unique(array('userid', 'jobid'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_jobs');
    }
}
