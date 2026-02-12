<?php
namespace zenhealth\models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $table = 'service';
    protected $primaryKey = 'numserv';
    public $timestamps = false;
    public $incrementing = false;

    public function reservations() {
        return $this->belongsToMany(
            'zenhealth\models\Reservation',
            'commande',
            'numserv',
            'numres'
        )->withPivot('nbrinterevntions');
    }
}