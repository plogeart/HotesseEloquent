<?php
namespace projet\classes\action;

class DefaultAction extends Action {
    public function executeGet(): string {
        return "<h3>Bienvenue dans l'espace sécurisé.</h3><p>Sélectionnez une action dans le menu ci-dessus.</p>";
    }
    public function executePost(): string {
        return $this->executeGet();
    }
}