<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

class Cabine extends Model {
    protected $table = 'cabine';
    protected $primaryKey = 'numcab';
    public $timestamps = false;
    protected $fillable = ['nbplace'];

    public function reservations() {
        return $this->hasMany(Reservation::class, 'numcab');
    }

    public function hotesses() {
        return $this->belongsToMany(Hotesse::class, 'affecter', 'numcab', 'numhot');
    }
}