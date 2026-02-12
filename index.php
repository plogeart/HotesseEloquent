<?php
session_start();

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

$isLogged = isset($_SESSION['user_id']);
$userGrade = $_SESSION['grade'] ?? null;
$isGestionnaire = ($userGrade === 'gestionnaire');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_form'])) {
    try {
        switch ($_POST['action_form']) {
            
            case 'login':
                $user = $manager->login((int)$_POST['login_id'], $_POST['login_pass']);
                $_SESSION['user_id'] = $user->numhot;
                $_SESSION['grade'] = $user->grade;
                $_SESSION['nom'] = $user->nomserv;
                $isLogged = true;
                $userGrade = $user->grade;
                $isGestionnaire = ($userGrade === 'gestionnaire');
                $message = "Bienvenue " . $user->nomserv . " (" . $user->grade . ")";
                break;

            case 'logout':
                session_destroy();
                header("Location: index.php");
                exit;

            case 'reserver':
                if (!$isLogged) throw new Exception("Veuillez vous connecter.");
                $res = $manager->reserverCabine((int)$_POST['numcab'], $_POST['datres'], (int)$_POST['nbpers']);
                $message = "SUCCÈS : Réservation n°" . $res->numres;
                break;

            case 'commander':
                if (!$isLogged) throw new Exception("Veuillez vous connecter.");
                $manager->commanderService((int)$_POST['numres'], (int)$_POST['numserv'], (int)$_POST['qte']);
                $message = "SUCCÈS : Service ajouté.";
                break;

            case 'affecter':
                if (!$isGestionnaire) throw new Exception("Droits insuffisants.");
                $manager->affecterHotesse((int)$_POST['numhot'], (int)$_POST['numcab']);
                $message = "SUCCÈS : Hôtesse affectée.";
                break;

            case 'encaisser':
                if (!$isGestionnaire) throw new Exception("Droits insuffisants.");
                $total = $manager->encaisserReservation((int)$_POST['numres'], $_POST['mode']);
                $message = "SUCCÈS : Paiement de $total € validé.";
                break;

            case 'modifier_service':
                if (!$isGestionnaire) throw new Exception("Droits insuffisants.");
                $prix = !empty($_POST['prix']) ? (float)$_POST['prix'] : null;
                $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : null;
                $manager->modifierService((int)$_POST['numserv'], $prix, $stock);
                $message = "SUCCÈS : Service mis à jour.";
                break;

            case 'annuler':
                if (!$isGestionnaire) throw new Exception("Droits insuffisants.");
                $manager->annulerReservation((int)$_POST['numres']);
                $message = "SUCCÈS : Réservation annulée.";
                break;
        }
    } catch (Exception $e) {
        $message = "ERREUR : " . $e->getMessage();
    }
}

$vue = $_GET['vue'] ?? 'accueil';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ZenHealth - Authentification Requise</title>
</head>
<body>

    <h1>Application ZenHealth</h1>

    <?php if (!$isLogged): ?>
        <fieldset>
            <legend>Connexion Requise</legend>
            <form method="POST" action="index.php">
                <input type="hidden" name="action_form" value="login">
                <p>Identifiant (ID) : <input type="number" name="login_id" required></p>
                <p>Mot de passe : <input type="password" name="login_pass" required></p>
                <button type="submit">Se connecter</button>
            </form>
            <?php if (!empty($message)) echo "<p style='color:red'>$message</p>"; ?>
        </fieldset>

    <?php else: ?>
        
        <div style="background:#eef; padding:10px; border:1px solid #ccc;">
            👤 Connecté en tant que : <strong><?= $_SESSION['nom'] ?></strong> (<?= ucfirst($_SESSION['grade']) ?>)
            <form method="POST" action="index.php" style="display:inline; float:right;">
                <input type="hidden" name="action_form" value="logout">
                <button type="submit">Déconnexion</button>
            </form>
        </div>

        <br>

        <fieldset>
            <legend>Menu Principal</legend>
            <form method="GET" action="index.php">
                <select name="vue">
                    <option value="accueil">-- Accueil --</option>
                    
                    <optgroup label="Gestion Clients">
                        <option value="reserver" <?= $vue == 'reserver' ? 'selected' : '' ?>>Réserver une cabine</option>
                        <option value="commander" <?= $vue == 'commander' ? 'selected' : '' ?>>Commander un service</option>
                    </optgroup>

                    <?php if ($isGestionnaire): ?>
                    <optgroup label="Administration (Gestionnaire)">
                        <option value="affecter" <?= $vue == 'affecter' ? 'selected' : '' ?>>Affecter une hôtesse</option>
                        <option value="encaisser" <?= $vue == 'encaisser' ? 'selected' : '' ?>>Encaisser réservation</option>
                        <option value="services" <?= $vue == 'services' ? 'selected' : '' ?>>Gérer les services</option>
                        <option value="annuler" <?= $vue == 'annuler' ? 'selected' : '' ?>>Annuler une réservation</option>
                    </optgroup>
                    <?php endif; ?>

                </select>
                <button type="submit">Accéder</button>
            </form>
        </fieldset>

        <hr>
        <?php if (!empty($message)) echo "<p style='background:#ddd; padding:5px;'><strong>Info :</strong> $message</p>"; ?>
        
        <?php if ($vue == 'accueil'): ?>
            <h3>Bienvenue dans l'espace sécurisé.</h3>
            <p>Sélectionnez une action ci-dessus.</p>

        <?php elseif ($vue == 'reserver'): ?>
            <h3>Nouvelle Réservation</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="reserver">
                Cabine: <input type="number" name="numcab" required><br>
                Date: <input type="text" name="datres" value="<?= date('Y-m-d H:i:s') ?>"><br>
                Nb Pers: <input type="number" name="nbpers" value="1"><br>
                <button type="submit">Valider</button>
            </form>

        <?php elseif ($vue == 'commander'): ?>
            <h3>Ajouter Service</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="commander">
                ID Résa: <input type="number" name="numres" required><br>
                ID Service: <input type="number" name="numserv" required><br>
                Quantité: <input type="number" name="qte" value="1"><br>
                <button type="submit">Ajouter</button>
            </form>

        <?php elseif ($isGestionnaire && $vue == 'affecter'): ?>
            <h3>Affectation</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="affecter">
                ID Hôtesse: <input type="number" name="numhot" required><br>
                ID Cabine: <input type="number" name="numcab" required><br>
                <button type="submit">Valider</button>
            </form>

        <?php elseif ($isGestionnaire && $vue == 'encaisser'): ?>
            <h3>Paiement</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="encaisser">
                ID Résa: <input type="number" name="numres" required><br>
                Mode: <select name="mode"><option>CB</option><option>Espèces</option></select><br>
                <button type="submit">Payer</button>
            </form>

        <?php elseif ($isGestionnaire && $vue == 'services'): ?>
            <h3>Modifier Service</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="modifier_service">
                ID Service: <input type="number" name="numserv" required><br>
                Prix: <input type="text" name="prix"><br>
                Stock: <input type="number" name="stock"><br>
                <button type="submit">Mettre à jour</button>
            </form>

        <?php elseif ($isGestionnaire && $vue == 'annuler'): ?>
            <h3>Annulation</h3>
            <form method="POST">
                <input type="hidden" name="action_form" value="annuler">
                ID Résa: <input type="number" name="numres" required><br>
                <button type="submit" style="color:red">Supprimer</button>
            </form>

        <?php endif; ?>

    <?php endif; ?>

</body>
</html>