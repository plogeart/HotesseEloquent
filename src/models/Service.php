<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $table = 'service';
    protected $primaryKey = 'numserv';
    public $timestamps = false;
    protected $fillable = ['libelle', 'prixunit', 'nbrinterventions'];

    public function reservations() {
        return $this->belongsToMany(Reservation::class, 'commande', 'numserv', 'numres')
                    ->withPivot('nbrinterevntions');
    }
}