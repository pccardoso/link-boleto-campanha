<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryPlate extends Model
{
    protected $table = 'history_plate';
    protected $fillable = [
        'authorized_plate_id',
        'status',
    ];
}
