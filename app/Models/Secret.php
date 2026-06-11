<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    protected $fillable = ['user_id','name','secret','expires_at','last_used','is_active'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
