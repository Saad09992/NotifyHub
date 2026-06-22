<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['user_id','template_body'];
    
    protected function casts():array {
        return [
        ];
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
