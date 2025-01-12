<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizes2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prizes2', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->integer('rate_min');
            $table->integer('rate_max');
            $table->string('type');
            $table->integer('is_prize');
            $table->integer('quantity');
            $table->integer('weight');
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
        Schema::dropIfExists('prizes2');
    }
}
