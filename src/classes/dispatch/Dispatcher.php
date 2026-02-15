<?php

namespace projet\classes\dispatch;

use projet\classes\action\DefaultAction;
use projet\classes\action\LoginAction;
use projet\classes\action\LogoutAction;
use projet\classes\action\ReserverAction;
use projet\classes\action\CommanderAction;
use projet\classes\action\AffecterAction;
use projet\classes\action\EncaisserAction;
use projet\classes\action\GererServicesAction;
use projet\classes\action\AnnulerAction;
use projet\classes\auth\AuthnProvider;

/**
 * Class Dispatcher
 * Analyse la requête, instancie l'Action correspondante et génère la page HTML.
 */
class Dispatcher {

    private string $actionQuery;

    public function __construct() {
        //Récupération de l'action
        $this->actionQuery = $_GET['action'] ?? 'default';
    }

    /**
     * Exécute le routage
     */
    public function run(): void {
        $isLogged = AuthnProvider::isSignedIn();

        if (!$isLogged) {
            //Si pas connecté, on force le login
            $action = new LoginAction();
        } else {
            //Routage
            switch ($this->actionQuery) {
                case 'logout':
                    $action = new LogoutAction();
                    break;
                case 'reserver':
                    $action = new ReserverAction();
                    break;
                case 'commander':
                    $action = new CommanderAction();
                    break;
                case 'affecter':
                    $action = new AffecterAction();
                    break;
                case 'encaisser':
                    $action = new EncaisserAction();
                    break;
                case 'services':
                    $action = new GererServicesAction();
                    break;
                case 'annuler':
                    $action = new AnnulerAction();
                    break;
                case 'default':
                default:
                    $action = new DefaultAction();
                    break;
            }
        }

        //Exécution de l'action et récupération de l'HTML
        try {
            $html = $action->execute();
        } catch (\Exception $e) {
            $html = "<div class='alert-erreur'>Une erreur est survenue : " . $e->getMessage() . "</div>";
            $html .= "<a href='?action=default'>Retour à l'accueil</a>";
        }

        //Rendu de la page complète
        $this->renderPage($html);
    }

    /**
     * Génère le squelette HTML
     * @param string $html Le contenu de l'action.
     */
    private function renderPage(string $html): void {
        $menu = "";

        //construction du menu si utilisateur connecté
        if (AuthnProvider::isSignedIn()) {
            $user = AuthnProvider::getSignedInUser();
            $nom = $user['nom'];
            $grade = $user['grade'];
            $gest = ($grade === 'gestionnaire');

            $menu = <<<HTML
            <div class='user'>
                <strong>$nom</strong> ($grade)
                <a href="?action=logout" class='btn-deco'>Déconnexion</a>
            </div>
            <fieldset>
                <legend>Menu</legend>
                <ul>
                    <li><a href="?action=default">Accueil</a></li>
                    <li><a href="?action=reserver">Réserver une cabine</a></li>
                    <li><a href="?action=commander">Commander un service</a></li>
            HTML;

            //Menu spécifique pour le gestionnaire
            if ($gest) {
                $menu .= <<<HTML
                    <hr>
                    <li><strong>Administration :</strong></li>
                    <li><a href="?action=affecter">Affecter une hôtesse</a></li>
                    <li><a href="?action=encaisser">Encaisser réservation</a></li>
                    <li><a href="?action=services">Gérer les services</a></li>
                    <li><a href="?action=annuler">Annuler une réservation</a></li>
                HTML;
            }
            $menu .= "</ul></fieldset><hr>";
        }

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>ZenHealth</title>
            <link rel="stylesheet" href="src/css/style.css">
        </head>
        <body>
            <h1>Application ZenHealth</h1>
            {$menu}
            <main>
                {$html}
            </main>
        </body>
        </html>
        HTML;
    }
}