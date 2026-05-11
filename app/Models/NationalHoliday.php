<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NationalHoliday extends Model
{
    protected $fillable = [
        'country_id', 'year', 'date', 'name', 'local_name',
        'type', 'observed_date', 'is_observed_shifted', 'description',
    ];

    protected $casts = [
        'date'                => 'date',
        'observed_date'       => 'date',
        'is_observed_shifted' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
