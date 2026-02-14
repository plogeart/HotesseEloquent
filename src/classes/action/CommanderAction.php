<?php
namespace projet\classes\action;

use projet\classes\repository\ZenManager;

class CommanderAction extends Action {

    public function executeGet(): string {
        return <<<HTML
        <h3>Ajouter un Service à une réservation</h3>
        <form method="POST" action="?action=commander">
            ID Réservation: <input type="number" name="numres" required><br><br>
            ID Service: <input type="number" name="numserv" required><br><br>
            Quantité: <input type="number" name="qte" value="1"><br><br>
            <button type="submit">Ajouter le service</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            ZenManager::commanderService((int)$_POST['numres'], (int)$_POST['numserv'], (int)$_POST['qte']);
            return "<div style='color:green; font-weight:bold;'>SUCCÈS : Service ajouté à la commande.</div>" . $this->executeGet();
        } catch (\Exception $e) {
            return "<div style='color:red'>ERREUR : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}