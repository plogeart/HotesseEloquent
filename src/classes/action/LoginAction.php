<?php
namespace projet\classes\action;

use projet\classes\auth\AuthnProvider;

class LoginAction extends Action {

    public function executeGet(): string {
        return <<<HTML
        <fieldset>
            <legend>Connexion Requise</legend>
            <form method="POST" action="?action=login">
                <p>Identifiant (ID) : <input type="number" name="login_id" required></p>
                <p>Mot de passe : <input type="password" name="login_pass" required></p>
                <button type="submit">Se connecter</button>
            </form>
        </fieldset>
        HTML;
    }

    public function executePost(): string {
        try {
            AuthnProvider::signin((int)$_POST['login_id'], $_POST['login_pass']);

            $action = new DefaultAction();
            return $action->execute();

        } catch (\Exception $e) {
            return "<p class='alert-erreur'>Erreur : " . $e->getMessage() . "</p>" . $this->executeGet();
        }
    }
}