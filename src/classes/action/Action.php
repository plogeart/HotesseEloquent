<?php

namespace projet\classes\action;

/**
 * Abstract Class Action
 * Base pour toutes les actions de l'application
 */
abstract class Action {

    protected ?string $http_method = null;

    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Point d'entrée de l'action. Dispatche vers executeGet ou executePost.
     */
    public function execute() : string {
        if ($this->http_method === "POST") {
            return $this->executePost();
        } else {
            return $this->executeGet();
        }
    }

    /**
     * Logique pour l'affichage (GET).
     */
    public abstract function executeGet() : string;

    /**
     * Logique pour le traitement de formulaire (POST).
     */
    public abstract function executePost() : string;
}