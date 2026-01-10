<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = ['client_id', 'start_at', 'end_at', 'status', 'notes'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
