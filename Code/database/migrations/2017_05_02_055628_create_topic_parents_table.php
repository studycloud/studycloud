<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_parent', function (Blueprint $table)
        {
            $table->integer('topic_id')->unsigned();
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id')->on('topics');
            $table->primary(['topic_id','parent_id']);
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
        Schema::dropIfExists('topic_parent');
    }
}
