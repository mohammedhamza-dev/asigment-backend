<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = ['customer_id', 'start_date', 'expire_date', 'payment', 'note', 'created_by'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
