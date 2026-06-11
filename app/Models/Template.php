<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['user_id','template_body','supported_channels'];
    
    protected function casts():array {
        return [
            'supported_channels'=>'array'
        ];
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
