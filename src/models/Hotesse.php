<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

class Hotesse extends Model {
    protected $table = 'hotesse';
    protected $primaryKey = 'numhot';
    public $timestamps = false;
    protected $fillable = ['email', 'passwd', 'nomserv', 'grade'];

    public function cabines() {
        return $this->belongsToMany(Cabine::class, 'affecter', 'numhot', 'numcab');
    }
}