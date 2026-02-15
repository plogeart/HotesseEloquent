<?php

namespace projet\classes\auth;

use projet\classes\exception\AuthnException;

/**
 * Class Authz
 * Gère les accès
 */
class Authz {

    /**
     * Vérifie que l'utilisateur a le role gestionnaire
     * @throws AuthnException Si droits insuffisants.
     */
    public static function checkRoleGestionnaire(): void {
        $user = AuthnProvider::getSignedInUser();

        if ($user['grade'] !== 'gestionnaire') {
            throw new AuthnException("Accès refusé : role 'gestionnaire' requis.");
        }
    }

    /**
     * Vérifie simplement si l'utilisateur est connecté.
     */
    public static function checkRoleHotesse(): void {
        if (!AuthnProvider::isSignedIn()) {
            throw new AuthnException("Accès refusé : connexion requise.");
        }
    }
}