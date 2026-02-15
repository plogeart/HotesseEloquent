<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model {
    protected $table = 'reservation';
    protected $primaryKey = 'numres';
    public $timestamps = false;
    public $incrementing = false;

    public function cabine() {
        return $this->belongsTo('projet\models\Cabine', 'numcab');
    }

    public function services() {
        return $this->belongsToMany('projet\models\Service', 'commande', 'numres', 'numserv')->withPivot('nbrinterevntions');
    }
}