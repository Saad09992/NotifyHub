<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguredChannel extends Model
{
    protected $fillable = ['user_id','channel','token'];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
