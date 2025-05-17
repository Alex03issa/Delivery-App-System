<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $fillable = ['send_by', 'send_to', 'message', 'message_type', 'date_time', 'is_received'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'send_by');
    }
}
