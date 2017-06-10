<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourceTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_topic', function (Blueprint $table) {
            $table->integer('resource_id');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->integer('topic_id');
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->primary(['resource_id','topic_id']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_topic');
    }
}
