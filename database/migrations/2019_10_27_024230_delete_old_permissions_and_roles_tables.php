<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteOldPermissionsAndRolesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('roles', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('role_user', function (Blueprint $table)
        {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->primary(['user_id','role_id']);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('permissions', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('permission_role', function (Blueprint $table)
        {
            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions');
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->primary(['permission_id','role_id']);
            $table->timestamp('created_at')->useCurrent();
        });
    }
}
