<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id','recipient_id','template_id','payload','status','channels'];
    
    protected function casts() {
        return [
            'payload'=>'array',
            'channels'=>'array'
        ];
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function template() {
        return $this->belongsTo(Template::class);
    }
    
    public function recipient() {
        return $this->belongsTo(Recipient::class);
    }
    
    public function notificationAttempts(){
        return $this->hasMany(NotificationAttempt::class);
    }
}
