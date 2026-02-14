<?php
namespace projet\classes\action;

use projet\classes\repository\ZenManager;

class ReserverAction extends Action {

    public function executeGet(): string {
        $date = date('Y-m-d H:i:s');
        return <<<HTML
        <h3>Nouvelle Réservation</h3>
        <form method="POST" action="?action=reserver">
            Cabine: <input type="number" name="numcab" required><br><br>
            Date: <input type="text" name="datres" value="$date"><br><br>
            Nb Pers: <input type="number" name="nbpers" value="1"><br><br>
            <button type="submit">Valider la réservation</button>
        </form>
        HTML;
    }

    public function executePost(): string {
        try {
            $res = ZenManager::reserverCabine((int)$_POST['numcab'], $_POST['datres'], (int)$_POST['nbpers']);
            return "<div style='color:green; font-weight:bold;'>SUCCÈS : Réservation effectuée (N° {$res->numres})</div>" . $this->executeGet();
        } catch (\Exception $e) {
            return "<div style='color:red'>Erreur : " . $e->getMessage() . "</div>" . $this->executeGet();
        }
    }
}