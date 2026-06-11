<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttempt extends Model
{
    protected $fillable = ['notification_id','channel','status','failure_reason'];
    
    public function notification(){
        return $this->belongsTo(Notification::class);
    }
}
