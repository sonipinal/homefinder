<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;   
use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    use HasFactory;                                         

    protected $fillable = ['community_id', 'address', 'price', 'bedrooms'];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}