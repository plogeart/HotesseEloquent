<?php
namespace projet\classes\action;

use projet\classes\auth\Authz;
use projet\classes\exception\AuthnException;
use projet\classes\repository\ZenManager;

class AffecterAction extends Action {

    public function executeGet(): string {
        try {
            Authz::checkRoleGestionnaire();
        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        }

        return <<<HTML
        <h3>Affectation Hôtesse / Cabine</h3>
        <form method="POST" action="?action=affecter">
            ID Hôtesse: <input type="number" name="numhot" required><br><br>
            ID Cabine: <input type="number" name="numcab" required><br><br>
            <button type="submit">Valider l'affectation</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            Authz::checkRoleGestionnaire();

            ZenManager::affecterHotesse((int)$_POST['numhot'], (int)$_POST['numcab']);
            return "<div class='alert-succes'>SUCCÈS : Hôtesse affectée.</div>" . $this->executeGet();

        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        } catch (\Exception $e) {
            return "<div class='alert-erreur'>ERREUR : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}