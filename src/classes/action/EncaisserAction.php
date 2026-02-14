<?php
namespace projet\classes\action;

use projet\classes\auth\Authz;
use projet\classes\exception\AuthnException;
use projet\classes\repository\ZenManager;

class EncaisserAction extends Action {

    public function executeGet(): string {
        try {
            Authz::checkRoleGestionnaire();
        } catch (AuthnException $e) {
            return "<p style='color:red'>" . $e->getMessage() . "</p>";
        }

        return <<<HTML
        <h3>Paiement Réservation</h3>
        <form method="POST" action="?action=encaisser">
            ID Réservation: <input type="number" name="numres" required><br><br>
            Mode de paiement: 
            <select name="mode">
                <option value="CB">Carte Bancaire</option>
                <option value="Espèces">Espèces</option>
                <option value="Chèque">Chèque</option>
            </select><br><br>
            <button type="submit">Encaisser</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            Authz::checkRoleGestionnaire();

            $total = ZenManager::encaisserReservation((int)$_POST['numres'], $_POST['mode']);
            return "<div style='color:green;'>SUCCÈS : Paiement de <strong>$total €</strong> validé.</div>" . $this->executeGet();
        } catch (AuthnException $e) {
            return "<p style='color:red'>" . $e->getMessage() . "</p>";
        } catch (\Exception $e) {
            return "<div style='color:red'>ERREUR : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}