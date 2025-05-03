<?php

// app/Models/Delivery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    // Table name (if different from the plural form of the model)
    protected $table = 'deliveries';

    // Fillable attributes
    protected $fillable = [
        'driver_id', // Assuming delivery is linked to a driver
        'client_id', // Assuming delivery is linked to a client
        'package_details', // Example field
        'delivery_status', // Example field
        'delivery_date', // Example field
        'total_price', // Example field
    ];

    // Define the relationship with the Driver model (adjust if needed)
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Define the relationship with the Client model (adjust if needed)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
