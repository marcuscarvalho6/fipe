<?php

namespace Marcuscarvalho6\Fipe\Entities;

use Illuminate\Database\Eloquent\Model;

class FipeBranch extends Model
{
    protected $table = 'fipe_branchs';
    protected $fillable = ['code', 'name'];
}