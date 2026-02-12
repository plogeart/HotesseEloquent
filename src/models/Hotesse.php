<?php
namespace zenhealth\models;

use Illuminate\Database\Eloquent\Model;

class Hotesse extends Model {
    protected $table = 'hotesse';
    protected $primaryKey = 'numhot';
    public $timestamps = false;
    public $incrementing = false;

    public function cabines() {
        return $this->belongsToMany(
            'zenhealth\models\Cabine',
            'affecter',
            'numhot',
            'numcab'
        )->withPivot('dataff');
    }
}