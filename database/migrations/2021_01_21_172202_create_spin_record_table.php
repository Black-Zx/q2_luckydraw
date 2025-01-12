<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spin_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('prize_id');
            $table->integer('tng_id');
            $table->string('scope', 50)->nullable();
            $table->string('trackip');
            $table->integer('status')->default('1');
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
        Schema::dropIfExists('spin_record');
    }
}
