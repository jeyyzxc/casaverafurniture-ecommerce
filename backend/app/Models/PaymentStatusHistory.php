<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatusHistory extends Model
{
    protected $table = 'payment_status_history';

    protected $fillable = [
        'payment_id',
        'status',
        'previous_status',
        'notes',
        'changed_by_type',
        'admin_id',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
