<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $table = 'service';
    protected $primaryKey = 'numserv';
    public $timestamps = false;

    public function reservations() {
        return $this->belongsToMany('projet\models\Reservation', 'commande', 'numserv', 'numres')->withPivot('nbrinterevntions');
    }
}