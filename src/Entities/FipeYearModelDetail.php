<?php

namespace Marcuscarvalho6\Fipe\Entities;

use Illuminate\Database\Eloquent\Model;

class FipeYearModelDetail extends Model
{
    protected $table = 'fipe_year_model_details';

    protected $fillable = [
        'fipe_year_model_id',
        'value',
        'marca',
        'modelo',
        'ano_modelo',
        'combustivel',
        'codigo_fipe',
        'mes_referencia',
        'autenticacao',
        'tipo_veiculo',
        'sigla_combustivel',
        'data_consulta'
    ];
}