<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('password');
            $table->string('state')->nullable();
            $table->string('region')->nullable();
            $table->string('area_type')->nullable();
            $table->string('dist_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('ethnic')->nullable();
            $table->string('top3')->nullable();
            $table->string('tier')->nullable();
            $table->string('vc')->nullable();
            $table->string('batch')->nullable();
            $table->integer('status')->default('0');
            $table->integer('available')->default('1');
            $table->integer('chance')->default('1');
            $table->integer('failed')->default('0');
            $table->dateTime('locked_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
