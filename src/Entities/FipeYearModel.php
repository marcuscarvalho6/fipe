<?php

namespace Marcuscarvalho6\Fipe\Entities;

use Illuminate\Database\Eloquent\Model;

class FipeYearModel extends Model
{
    protected $table = 'fipe_year_models';
    protected $fillable = ['model_code', 'name', 'value'];
}