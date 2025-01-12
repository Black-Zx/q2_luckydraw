<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyEntryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_entry', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('Q1')->default('-1');
            $table->string('Q2')->default('-1');
            $table->string('Q3')->default('-1');
            $table->string('Q4')->default('-1');
            $table->string('Q5')->default('-1');
            $table->string('Q6')->default('-1');
            $table->string('Q7')->default('-1');
            $table->string('Q8')->default('-1');
            $table->string('Q9')->default('-1');
            $table->string('Q10')->default('-1');
            $table->string('Q11')->default('-1');
            $table->string('Q12')->default('-1');
            $table->string('Q13')->default('-1');
            $table->string('Q14')->default('-1');
            $table->string('Q15')->default('-1');
            $table->dateTime('Q1_updated')->nullable();
            $table->dateTime('Q2_updated')->nullable();
            $table->dateTime('Q3_updated')->nullable();
            $table->dateTime('Q4_updated')->nullable();
            $table->dateTime('Q5_updated')->nullable();
            $table->dateTime('Q6_updated')->nullable();
            $table->dateTime('Q7_updated')->nullable();
            $table->dateTime('Q8_updated')->nullable();
            $table->dateTime('Q9_updated')->nullable();
            $table->dateTime('Q10_updated')->nullable();
            $table->dateTime('Q11_updated')->nullable();
            $table->dateTime('Q12_updated')->nullable();
            $table->dateTime('Q13_updated')->nullable();
            $table->dateTime('Q14_updated')->nullable();
            $table->dateTime('Q15_updated')->nullable();
            $table->integer('score')->default('0');
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
        Schema::dropIfExists('survey_entry');
    }
}
