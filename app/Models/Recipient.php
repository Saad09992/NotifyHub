<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $fillable = ['user_id','contact_details'];
    
    protected function casts(){
        return [
            'contact_details'=>'array'
        ];
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
    
}
