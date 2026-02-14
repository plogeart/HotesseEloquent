<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use projet\classes\dispatch\Dispatcher;

session_start();

//Initialisation de la BDD
$conf = parse_ini_file('src/conf/conf.ini');

$db = new DB;
$db->addConnection([
    'driver'    => 'mysql',
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

//Lancement du Dispatcher
$dispatcher = new Dispatcher();
$dispatcher->run();