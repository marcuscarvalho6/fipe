<?php

namespace Marcuscarvalho6\Fipe\Entities;

use Illuminate\Database\Eloquent\Model;

class FipeModel extends Model
{
    protected $table = 'fipe_models';
    protected $fillable = ['code', 'name'];
}