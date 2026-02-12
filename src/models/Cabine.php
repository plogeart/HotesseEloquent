<?php
namespace zenhealth\models;

use Illuminate\Database\Eloquent\Model;

class Cabine extends Model {
    protected $table = 'cabine';
    protected $primaryKey = 'numcab';
    public $timestamps = false;
    public $incrementing = false;

    public function reservations() {
        return $this->hasMany('zenhealth\models\Reservation', 'numcab');
    }

    public function hotesses() {
        return $this->belongsToMany(
            'zenhealth\models\Hotesse', 
            'affecter', 
            'numcab', 
            'numhot'
        )->withPivot('dataff');
    }
}