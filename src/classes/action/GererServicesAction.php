<?php
namespace projet\classes\action;

use projet\classes\auth\Authz;
use projet\classes\exception\AuthnException;
use projet\classes\repository\ZenManager;

class GererServicesAction extends Action {

    public function executeGet(): string {
        try {
            Authz::checkRoleGestionnaire();
        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        }

        return <<<HTML
        <h3>Modifier un Service</h3>
        <p>Laissez un champ vide pour ne pas le modifier.</p>
        <form method="POST" action="?action=services">
            ID Service à modifier: <input type="number" name="numserv" required><br><br>
            Nouveau Prix: <input type="text" name="prix" placeholder="Ex: 45.50"><br><br>
            Nouveau Stock (interventions/jour): <input type="number" name="stock"><br><br>
            <button type="submit">Mettre à jour</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            Authz::checkRoleGestionnaire();

            $prix = !empty($_POST['prix']) ? (float)$_POST['prix'] : null;
            $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : null;

            ZenManager::modifierService((int)$_POST['numserv'], $prix, $stock);

            return "<div class='alert-succes'>SUCCÈS : Service mis à jour.</div>" . $this->executeGet();
        } catch (AuthnException $e) {
            return "<p class='alert-erreur'>" . $e->getMessage() . "</p>";
        } catch (\Exception $e) {
            return "<div class='alert-erreur'>ERREUR : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}