<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFipeYearModelDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fipe_year_model_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fipe_year_model_id')->unsigned();
            $table->float('value', 10, 2);
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->string('ano_modelo', 10);
            $table->string('combustivel', 10);
            $table->string('codigo_fipe', 10);
            $table->string('mes_referencia');
            $table->string('autenticacao');
            $table->integer('tipo_veiculo');
            $table->string('sigla_combustivel', 2);
            $table->string('data_consulta');
            $table->timestamps();
            $table->foreign('fipe_year_model_id')->references('id')->on('fipe_year_models');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fipe_year_model_details');
    }
}
