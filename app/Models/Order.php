<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = ['client_id', 'total', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}