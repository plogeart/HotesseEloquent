<?php
namespace projet\classes\action;

use projet\classes\auth\Authz;
use projet\classes\exception\AuthnException;
use projet\classes\repository\ZenManager;

class AnnulerAction extends Action {

    public function executeGet(): string {
        try {
            Authz::checkRoleGestionnaire();
        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        }

        return <<<HTML
        <h3>Annuler une réservation</h3>
        <div class='alert-attention'>
            Attention: Cette action est irréversible.
        </div>
        <br><br>
        <form method="POST" action="?action=annuler">
            ID Réservation: <input type="number" name="numres" required><br><br>
            <button type="submit" class='btn-danger'>Supprimer la réservation</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            Authz::checkRoleGestionnaire();

            ZenManager::annulerReservation((int)$_POST['numres']);
            return "<div class='alert-succes'>SUCCÈS : La réservation a été annulée et supprimée.</div>" . $this->executeGet();
        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        } catch (\Exception $e) {
            return "<div class='alert-erreur'>ERREUR : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}