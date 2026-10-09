<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $fillable = ['name', 'city'];

    public function homes()
    {
        return $this->hasMany(Home::class);
    }
}
