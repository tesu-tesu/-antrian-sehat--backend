<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthAgency extends Model
{
    protected $fillable = [
        'name', 'address', 'image', 'call_center', 'email',
    ];

    public function polyclinics() {
        return $this->hasMany('App\Polyclinic');
    }
}
