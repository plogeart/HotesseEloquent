<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model {
    protected $table = 'reservation';
    protected $primaryKey = 'numres';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['numres', 'numcab', 'datres', 'nbpers', 'datpaie', 'modpaie', 'montcom'];

    public function cabine() {
        return $this->belongsTo(Cabine::class, 'numcab');
    }

    public function services() {
        return $this->belongsToMany(Service::class, 'commande', 'numres', 'numserv')
                    ->withPivot('nbrinterevntions');
    }
}