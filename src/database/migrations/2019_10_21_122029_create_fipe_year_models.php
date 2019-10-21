<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFipeYearModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fipe_year_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('model_code');
            $table->string('name');
            $table->string('value');
            $table->timestamps();
            $table->foreign('model_code')->references('code')->on('fipe_models');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fipe_year_models');
    }
}
