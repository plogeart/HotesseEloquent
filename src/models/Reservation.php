<?php
namespace zenhealth\models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model {
    protected $table = 'reservation';
    protected $primaryKey = 'numres';
    public $timestamps = false;
    public $incrementing = false;

    protected $dates = ['datres', 'datpaie'];

    public function cabine() {
        return $this->belongsTo('zenhealth\models\Cabine', 'numcab');
    }

    public function services() {
        return $this->belongsToMany(
            'zenhealth\models\Service',
            'commande',
            'numres',
            'numserv'
        )->withPivot('nbrinterevntions');
    }
}