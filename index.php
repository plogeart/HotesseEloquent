<?php
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use zenhealth\models\Cabine;
use zenhealth\models\Service;
use zenhealth\models\Hotesse;
use zenhealth\models\Reservation;

$conf = parse_ini_file('src/conf/conf.ini');

$db = new DB();
$db->addConnection([
    'driver'    => $conf['driver'],
    'host'      => $conf['host'],
    'database'  => $conf['database'],
    'username'  => $conf['username'],
    'password'  => $conf['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$db->setAsGlobal();
$db->bootEloquent();

DB::connection()->enableQueryLog();

echo "<h1>Projet ZenHealth - Gestion Institut</h1>";

echo "<h2>État actuel de la base</h2>";

$hotesses = Hotesse::all();
echo "<h3>Personnel :</h3><ul>";
foreach ($hotesses as $h) {
    echo "<li>{$h->nomserv} ({$h->grade})</li>";
}
echo "</ul>";

$reservations = Reservation::with(['cabine', 'services'])->get();
echo "<h3>Réservations en cours :</h3><ul>";
foreach ($reservations as $r) {
    echo "<li>Res n°{$r->numres} - Cabine {$r->numcab} - Client: {$r->nbpers} pers.";
    echo "<ul>";
    foreach ($r->services as $s) {
        echo "<li>Service: {$s->libelle} (x" . $s->pivot->nbrinterevntions . ")</li>";
    }
    echo "</ul></li>";
}
echo "</ul>";

echo "<h2>Test Transaction : Nouvelle Réservation</h2>";

try {
    DB::beginTransaction();

    $newRes = new Reservation();
    $newRes->numres = 200;
    $newRes->numcab = 12;
    $newRes->datres = date('Y-m-d H:i:s');
    $newRes->nbpers = 1;
    $newRes->modpaie = 'En attente';
    $newRes->save();

    echo "Réservation 200 créée.<br>";

    $serviceVisage = Service::find(1);
    
    $newRes->services()->attach($serviceVisage->numserv, ['nbrinterevntions' => 1]);
    
    echo "Service 'Soins visage' ajouté à la réservation.<br>";

    if ($serviceVisage->nbrinterventions > 0) {
        $serviceVisage->nbrinterventions -= 1;
        $serviceVisage->save();
        echo "Stock intervention décrémenté.<br>";
    } else {
        throw new Exception("Plus de disponibilité pour ce service !");
    }

    DB::commit();
    echo "<strong>Transaction validée avec succès !</strong>";

} catch (Exception $e) {
    DB::rollback();
    echo "<strong>Erreur : Transaction annulée.</strong> " . $e->getMessage();
}