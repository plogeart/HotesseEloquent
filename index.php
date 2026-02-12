<?php
// Affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use projet\classes\ZenManager;
use projet\models\Service;
use projet\models\Cabine;
use projet\models\Hotesse;
use projet\models\Reservation;

// --- 1. INITIALISATION BDD ---
$conf = parse_ini_file('src/conf/conf.ini');
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $conf['host'],
    'database'  => $conf['database'],
    'username'  => $conf['username'],
    'password'  => $conf['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$manager = new ZenManager();
$message = "";

// --- 2. TRAITEMENT DES ACTIONS (POST) ---
// On traite le formulaire s'il a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_form'])) {
    try {
        switch ($_POST['action_form']) {
            case 'reserver':
                $res = $manager->reserverCabine((int)$_POST['numcab'], $_POST['datres'], (int)$_POST['nbpers']);
                $message = "SUCCÈS : Réservation créée avec le numéro " . $res->numres;
                break;

            case 'commander':
                $manager->commanderService((int)$_POST['numres'], (int)$_POST['numserv'], (int)$_POST['qte']);
                $message = "SUCCÈS : Service ajouté à la commande.";
                break;

            case 'affecter':
                $manager->affecterHotesse((int)$_POST['numhot'], (int)$_POST['numcab']);
                $message = "SUCCÈS : Hôtesse affectée.";
                break;

            case 'encaisser':
                $total = $manager->encaisserReservation((int)$_POST['numres'], $_POST['mode']);
                $message = "SUCCÈS : Paiement effectué. Montant : $total €";
                break;

            case 'modifier_service':
                $prix = !empty($_POST['prix']) ? (float)$_POST['prix'] : null;
                $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : null;
                $manager->modifierService((int)$_POST['numserv'], $prix, $stock);
                $message = "SUCCÈS : Service mis à jour.";
                break;

            case 'annuler':
                $manager->annulerReservation((int)$_POST['numres']);
                $message = "SUCCÈS : Réservation annulée.";
                break;
        }
    } catch (Exception $e) {
        $message = "ERREUR : " . $e->getMessage();
    }
}

// Quelle vue afficher ? (Par défaut 'accueil')
$vue = $_GET['vue'] ?? 'accueil';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ZenHealth (Brut)</title>
</head>
<body>

    <h1>Application ZenHealth</h1>

    <fieldset>
        <legend>Menu Principal</legend>
        <form method="GET" action="index.php">
            <label>Que voulez-vous faire ?</label>
            <select name="vue">
                <option value="accueil" <?= $vue == 'accueil' ? 'selected' : '' ?>>-- Accueil / État --</option>
                <option value="reserver" <?= $vue == 'reserver' ? 'selected' : '' ?>>1. Réserver une cabine</option>
                <option value="commander" <?= $vue == 'commander' ? 'selected' : '' ?>>2. Commander un service</option>
                <option value="affecter" <?= $vue == 'affecter' ? 'selected' : '' ?>>3. Affecter une hôtesse</option>
                <option value="encaisser" <?= $vue == 'encaisser' ? 'selected' : '' ?>>4. Encaisser une réservation</option>
                <option value="services" <?= $vue == 'services' ? 'selected' : '' ?>>5. Gérer les Services</option>
                <option value="annuler" <?= $vue == 'annuler' ? 'selected' : '' ?>>6. Annuler une réservation</option>
            </select>
            <button type="submit">Valider</button>
        </form>
    </fieldset>

    <hr>

    <?php if (!empty($message)): ?>
        <p style="background: #eee; padding: 10px; border: 1px solid #000;">
            <strong>Message du système :</strong> <?= $message ?>
        </p>
        <hr>
    <?php endif; ?>


    <?php if ($vue == 'accueil'): ?>
        <h2>État de l'institut</h2>
        <p>Voici les réservations en attente de paiement :</p>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Cabine</th>
                <th>Personnes</th>
            </tr>
            <?php 
            $resas = Reservation::whereNull('datpaie')->get();
            foreach($resas as $r): ?>
            <tr>
                <td><?= $r->numres ?></td>
                <td><?= $r->datres ?></td>
                <td><?= $r->numcab ?></td>
                <td><?= $r->nbpers ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($vue == 'reserver'): ?>
        <h2>Nouvelle Réservation</h2>
        <form method="POST" action="index.php?vue=reserver">
            <input type="hidden" name="action_form" value="reserver">
            
            <p>
                <label>Numéro Cabine :</label><br>
                <input type="number" name="numcab" required>
            </p>
            <p>
                <label>Date (AAAA-MM-JJ HH:MM:SS) :</label><br>
                <input type="text" name="datres" value="<?= date('Y-m-d H:i:s') ?>" required>
            </p>
            <p>
                <label>Nombre de personnes :</label><br>
                <input type="number" name="nbpers" value="1" required>
            </p>
            <button type="submit">Créer la réservation</button>
        </form>

    <?php elseif ($vue == 'commander'): ?>
        <h2>Ajout de Service (Commande)</h2>
        <form method="POST" action="index.php?vue=commander">
            <input type="hidden" name="action_form" value="commander">

            <p>
                <label>ID Réservation :</label><br>
                <input type="number" name="numres" required>
            </p>
            <p>
                <label>Service :</label><br>
                <select name="numserv">
                    <?php foreach(Service::all() as $s): ?>
                        <option value="<?= $s->numserv ?>">
                            <?= $s->libelle ?> (<?= $s->prixunit ?>€) - Stock: <?= $s->nbrinterventions ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>Quantité :</label><br>
                <input type="number" name="qte" value="1" required>
            </p>
            <button type="submit">Ajouter</button>
        </form>

    <?php elseif ($vue == 'affecter'): ?>
        <h2>Affectation Hôtesse</h2>
        <form method="POST" action="index.php?vue=affecter">
            <input type="hidden" name="action_form" value="affecter">

            <p>
                <label>Hôtesse :</label><br>
                <select name="numhot">
                    <?php foreach(Hotesse::all() as $h): ?>
                        <option value="<?= $h->numhot ?>"><?= $h->nomserv ?> (<?= $h->grade ?>)</option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>Cabine :</label><br>
                <select name="numcab">
                    <?php foreach(Cabine::all() as $c): ?>
                        <option value="<?= $c->numcab ?>">Cabine <?= $c->numcab ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <button type="submit">Valider l'affectation</button>
        </form>

    <?php elseif ($vue == 'encaisser'): ?>
        <h2>Paiement</h2>
        <form method="POST" action="index.php?vue=encaisser">
            <input type="hidden" name="action_form" value="encaisser">

            <p>
                <label>ID Réservation :</label><br>
                <input type="number" name="numres" required>
            </p>
            <p>
                <label>Mode de paiement :</label><br>
                <select name="mode">
                    <option>Carte Bancaire</option>
                    <option>Espèces</option>
                    <option>Chèque</option>
                </select>
            </p>
            <button type="submit">Payer</button>
        </form>

    <?php elseif ($vue == 'services'): ?>
        <h2>Gestion des Services</h2>
        <form method="POST" action="index.php?vue=services">
            <input type="hidden" name="action_form" value="modifier_service">

            <p>
                <label>Choisir le service :</label><br>
                <select name="numserv">
                    <?php foreach(Service::all() as $s): ?>
                        <option value="<?= $s->numserv ?>"><?= $s->libelle ?> (Actuel: <?= $s->prixunit ?>€)</option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>Nouveau Prix (laisser vide si inchangé) :</label><br>
                <input type="text" name="prix">
            </p>
            <p>
                <label>Nouveau Stock (laisser vide si inchangé) :</label><br>
                <input type="number" name="stock">
            </p>
            <button type="submit">Mettre à jour</button>
        </form>

    <?php elseif ($vue == 'annuler'): ?>
        <h2>Annulation</h2>
        <form method="POST" action="index.php?vue=annuler">
            <input type="hidden" name="action_form" value="annuler">
            <p>
                <label>ID Réservation :</label><br>
                <input type="number" name="numres" required>
            </p>
            <button type="submit">Supprimer la réservation</button>
        </form>

    <?php endif; ?>

</body>
</html>