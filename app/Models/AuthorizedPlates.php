<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HistoryPlate;

class AuthorizedPlates extends Model
{

    protected $table = 'authorized_plates';

    protected $fillable = [
        'name',
        'plate_number',
        'vehicle_number',
        'contract_date',
        'vehicle_status',
        'vehicle_type',
        'volunteer',
        'email',
        'phone',
        'cell_phone',
        'fixed_value',
        'agreement_value'
    ];

    public function history(){
        return $this->hasMany(HistoryPlate::class, 'authorized_plate_id', 'id');
    }

}
