<?php

namespace App\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
	public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}