<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Cabine extends Model {
    protected $table = 'cabine';
    protected $primaryKey = 'numcab';
    public $timestamps = false;

    public function reservations() {
        return $this->hasMany('projet\models\Reservation','numcab');
    }

    public function hotesses() {
        return $this->belongsToMany('projet\models\Hotesse', 'affecter', 'numcab', 'numhot');
    }
}