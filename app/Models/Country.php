<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'iso2', 'iso3', 'phone_code', 'capital', 'currency', 'currency_symbol', 'latitude', 'longitude', 'region', 'subregion'];

    public function holidays()
    {
        return $this->hasMany(NationalHoliday::class);
    }
}
