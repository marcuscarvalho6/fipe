<?php

namespace Marcuscarvalho6\Fipe\Entities;

use Illuminate\Database\Eloquent\Model;

class FipeReference extends Model
{
    protected $table = 'fipe_references';
    protected $fillable = ['code', 'month'];
}