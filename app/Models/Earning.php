<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'earnings';

    // Define the fillable fields to allow mass assignment
    protected $fillable = [
        'driver_id',
        'delivery_id',
        'total_revenue',
        'commission',
        'pending_payment',
    ];

    // Define the relationships
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
