<?php

namespace projet\models;

use Illuminate\Database\Eloquent\Model;

class Hotesse extends Model {
    protected $table = 'hotesse';
    protected $primaryKey = 'numhot';
    public $timestamps = false;

    public function cabines() {
        return $this->belongsToMany('projet\models\Cabine', 'affecter', 'numhot', 'numcab');
    }
}